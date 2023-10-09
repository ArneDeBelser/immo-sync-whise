<?php

namespace ADB\ImmoSyncWhise\Services;

use ADB\ImmoSyncWhise\Adapter\EstateAdapter;
use ADB\ImmoSyncWhise\Model\Estate;
use ADB\ImmoSyncWhise\Parser\EstateParser;
use ADB\ImmoSyncWhise\Services\Service;

class EstateSyncService extends Service
{
    public function __construct(
        private Estate $estate,
        private EstateAdapter $estateAdapter,
        private EstateParser $estateParser
    ) {
    }

    public function syncAllEstates(): void
    {
        \WP_CLI::log("Fetching all estates from Whise API");
        $this->operationsLogger->info("Fetching all estates from Whise API");

        $estates = $this->estateAdapter->list([
            'LanguageId' => $_ENV['LANG'],
        ]);

        foreach ($estates as $estate) {
            // Save the Post
            $postId = $this->estate->save($model);

            // Configure the parsers
            $this->estateParser->setMethod('add_post_meta');
            $this->estateParser->setPostId($postId);
            $this->estateParser->setObject($model);

            // Parse the response object
            $this->estateParser->parseProperties();
            $this->estateParser->parseDetails();
            $this->estateParser->parsePictures();

            \WP_CLI::success("Fetched estate, created post {$postId}");
            $this->operationsLogger->info("Fetched estate, created post {$postId}");
        }

        \WP_CLI::success('Fetching successful');
        $this->operationsLogger->info("Fetching successful");
    }
}
