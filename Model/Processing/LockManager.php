<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

class LockManager
{
    private \M2E\Otto\Model\Processing $processing;
    private \M2E\Otto\Model\Processing\LockFactory $lockFactory;
    private \M2E\Otto\Model\Processing\Lock\Repository $lockRepository;

    public function __construct(
        \M2E\Otto\Model\Processing $processing,
        LockFactory $lockFactory,
        \M2E\Otto\Model\Processing\Lock\Repository $lockRepository
    ) {
        $this->processing = $processing;
        $this->lockFactory = $lockFactory;
        $this->lockRepository = $lockRepository;
    }

    public function create(string $nick, int $objId): Lock
    {
        $lock = $this->lockFactory->create();
        $lock->create($this->processing->getId(), $nick, $objId);

        $this->lockRepository->create($lock);

        return $lock;
    }

    public function delete(string $nick, int $objId): void
    {
        $lock = $this->lockRepository->findByProcessingAndNickAndId($this->processing, $nick, $objId);
        if ($lock === null) {
            return;
        }

        $this->lockRepository->remove($lock);
    }
}
