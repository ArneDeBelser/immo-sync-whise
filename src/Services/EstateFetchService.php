<?php

namespace ADB\ImmoSyncWhise\Services;

use ADB\ImmoSyncWhise\Adapter\EstateAdapter;
use ADB\ImmoSyncWhise\Model\Estate;
use ADB\ImmoSyncWhise\Parser\EstateParser;
use ADB\ImmoSyncWhise\Services\Contracts\ServiceContract;
use Psr\Log\LoggerInterface;

class EstateFetchService implements ServiceContract
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
        \WP_CLI::line("Fetching all estates from Whise API");
        $this->logger->info("Fetching all estates from Whise API");

        $estates = $this->estateAdapter->list(['LanguageId' => $_ENV['LANG']]);

        foreach ($estates as $estate) {
            $postId = $this->estate->save($estate);

            $this->estateParser->setMethod('add_post_meta')->setPostId($postId)->setObject($estate);

            $this->estateParser->parseProperties();
            $this->estateParser->parseDetails();
            $this->estateParser->parsePictures("urlXXL");

            \WP_CLI::success("Fetched estate, created estate with post ID #{$postId}");
            $this->logger->info("Fetched estate, created estate with post ID #{$postId}");
        }

        \WP_CLI::success('Fetching successful');
        $this->logger->info("Fetching successful");
    }
}
