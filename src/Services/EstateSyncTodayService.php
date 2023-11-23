<?php

namespace ADB\ImmoSyncWhise\Services;

use ADB\ImmoSyncWhise\Adapter\EstateAdapter;
use ADB\ImmoSyncWhise\Model\Estate;
use ADB\ImmoSyncWhise\Parser\EstateParser;
use ADB\ImmoSyncWhise\Services\Contracts\ServiceContract;
use Psr\Log\LoggerInterface;

class EstateSyncTodayService implements ServiceContract
{
    public function __construct(
        private Estate $estate,
        private EstateAdapter $estateAdapter,
        private EstateParser $estateParser,
        public  LoggerInterface $logger,
    ) {
    }

    public function run(): void
    {
        $yesterday = date("Y-m-d H:i:s", strtotime("yesterday"));

        \WP_CLI::log("Syncing all estates which have been added or edited since: {$yesterday}");
        $this->logger->info("Syncing all estates which have been added or edited since: {$yesterday}");

        $estates = $this->estateAdapter->list([
            'LanguageId' => $_ENV['LANG'],
            'UpdateDateTimeRange' => [
                'Min' => $yesterday
            ]
        ]);

        foreach ($estates as $estate) {
            $this->estate->exists($estate->getData()['id']) ? $this->handleUpdate($estate) : $this->handleSave($estate);
            // $this->logger->warning(json_encode($estate->getData()['name']));
            // $this->logger->warning(json_encode($estate->getData()['updateDateTime']));
        }

        \WP_CLI::success('Syncing successful');
        $this->logger->info("Syncing successful");
    }

    private function handleSave($estate)
    {
        // Save the Post
        $postId = $this->estate->save($estate);

        // Configure the parser
        $this->estateParser->setMethod('add_post_meta');
        $this->estateParser->setPostId($postId);
        $this->estateParser->setObject($estate);

        // Parse the response object
        $this->estateParser->parseProperties();
        $this->estateParser->parseDetails();
        $this->estateParser->parsePictures("urlXXL");

        \WP_CLI::success("Imported estate, created estate with post ID {$postId}");
        $this->logger->info("Imported estate, created estate with post ID  {$postId}");
    }

    private function handleUpdate($estate)
    {
        // Update the Post
        $postId = $this->estate->update($estate);

        // Configure the parser
        $this->estateParser->setMethod('update_post_meta');
        $this->estateParser->setPostId($postId);
        $this->estateParser->setObject($estate);

        // Parse the response object
        $this->estateParser->parseProperties();
        $this->estateParser->removeDetails();
        $this->estateParser->parseDetails();
        $this->estateParser->removePictures();
        $this->estateParser->parsePictures("urlXXL");

        \WP_CLI::success("Synced estate, updated estate with post ID  {$postId}");
        $this->logger->info("Synced estate, updated estate with post ID  {$postId}");
    }
}
