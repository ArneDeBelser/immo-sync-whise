<?php

namespace ADB\ImmoSyncWhise\Parser;

use ADB\ImmoSyncWhise\Database\Database;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Psr\Log\LoggerInterface;
use Throwable;

class EstateParser
{
    protected $method;

    protected $postId;

    protected $estateResponse;

    public function __construct(public LoggerInterface $logger, private Client $client, private Database $db)
    {
    }

    public function setMethod(string $method): EstateParser
    {
        $this->method = $method;

        return $this;
    }

    public function setPostId(int $postId): EstateParser
    {
        $this->postId = $postId;

        return $this;
    }

    public function setObject($response): EstateParser
    {
        $this->estateResponse = $response;

        return $this;
    }

    public function parseProperties()
    {
        foreach ($this->estateResponse->getData() as $key => $value) {
            if ($key === 'details' || $key === 'pictures') return;

            if (gettype($value) == 'object') {
                if (isset($value->date)) $this->parse($key, $value, 'date');
                if (isset($value->id)) $this->parse($key, $value, 'id');
            } else {
                if ($key == 'shortDescription') {
                    $this->parseLine($key, $value[0]->content ?: null); // shortDescription contains Whise\Api\Response\ResponseObject
                } else {
                    $this->parseLine($key, $value);
                }
            }

            if (is_array($value)) $this->parseArray($key, $value[0], 'content');
        }
    }

    /**
     * Ayns retrieve pictures from Whise api and parse them into WP
     *
     * @param string $pictureDimension Choose between "urlSmall" or "urlLarge" or "urlXXL", choosing "urlSmall" will provide the fastest response
     * 
     * @return void
     */
    public function parsePictures($pictureDimension)
    {
        try {
            $promises = [];

            foreach ($this->estateResponse->pictures as $pictureInfo) {
                $promises[] = $this->client->getAsync($pictureInfo->$pictureDimension);
            }

            $responses = Promise\Utils::unwrap($promises);

            foreach ($responses as $key => $response) {
                if ($response instanceof \WP_Error) {
                    continue;
                }

                $attachmentId = $this->saveImageToPost($response, $this->postId);

                if ($key === 0) {
                    set_post_thumbnail($this->postId, $attachmentId);
                }
            }
        } catch (Throwable $e) {
            $error = json_encode($e->getMessage());

            $this->logger->error("There was an error when saving estate pictures {$error} for {$this->postId}");
        }
    }

    private function saveImageToPost($response, int $postId): int
    {
        $filename = basename(uniqid() . '.jpg');
        $uploadDir = wp_upload_dir();
        $filePath = $uploadDir['path'] . '/' . $filename;

        // Save the image file
        file_put_contents($filePath, $response->getBody());

        // Create the attachment data
        $attachment = [
            'guid'           => $uploadDir['url'] . '/' . $filename,
            'post_mime_type' => $response->getHeaderLine('Content-Type'),
            'post_title'     => $filename,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        // Insert the attachment
        $attachmentId = wp_insert_attachment($attachment, $filePath, $postId);

        // Generate the attachment metadata
        $attachmentData = wp_generate_attachment_metadata($attachmentId, $filePath);
        wp_update_attachment_metadata($attachmentId, $attachmentData);

        return $attachmentId;
    }

    public function removePictures()
    {
        /** @var WP_Post[] $images */
        $images = get_attached_media('image', $this->postId);

        foreach ($images as $image) {
            wp_delete_attachment($image->ID, true);
        }
    }

    public function parseDetails()
    {
        try {
            foreach ($this->estateResponse->details as $detailGroup) {
                $this->db->save($this->postId, $detailGroup->getData());
            }
        } catch (Throwable $e) {
            $error = json_encode($e->getMessage());

            $this->logger->error("There was an error when saving estate details {$error} for {$this->postId}");
        }
    }

    public function removeDetails()
    {
        $this->db->delete($this->postId);
    }

    private function parse($key, $value, $property)
    {
        call_user_func_array(
            $this->method,
            [
                $this->postId, '_iws_' . $key,
                $value->{$property},
                $this->method == 'add_post_meta' ? true : ''
            ]
        );

        $this->addShowMetaField($key);
    }

    private function parseArray($key, $value, $property)
    {
        call_user_func_array(
            $this->method,
            [
                $this->postId,
                '_iws_' . $key,
                $value->getData()[$property],
                $this->method == 'add_post_meta' ? true : ''
            ]
        );

        $this->addShowMetaField($key);
    }

    private function parseLine($key, $value): void
    {
        call_user_func_array(
            $this->method,
            [
                $this->postId,
                '_iws_' . $key,
                $value,
                $this->method == 'add_post_meta' ? true : ''
            ]
        );

        $this->addShowMetaField($key);
    }

    /**
     * Add "show" meta field with value of "0" as in, not visible
     *
     * @param string $key
     * @return void
     */
    private function addShowMetaField($key)
    {
        call_user_func_array($this->method, [$this->postId, '_show_iws_' . $key,  1, $this->method == 'add_post_meta' ? true : '']);
    }
}
