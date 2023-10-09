<?php

namespace ADB\ImmoSyncWhise\Model\Contracts;

interface ModelContract
{
    public function save(int $id): int|\WP_Error;

    public function update($model): int|\WP_Error;

    public function saveMeta($postId, $estate): void;

    public function updateMeta($postId, $estate): void;

    public function getMetaFields($postId): array;

    public function exists($providerId): bool;
}