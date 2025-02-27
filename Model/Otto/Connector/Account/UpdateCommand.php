<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account;

class UpdateCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private const ERROR_CODE_INSTALL_NOT_FOUND = 4040;

    private string $title;
    private string $accountHash;
    private string $authCode;

    public function __construct(string $title, string $accountHash, string $authCode)
    {
        $this->title = $title;
        $this->accountHash = $accountHash;
        $this->authCode = $authCode;
    }

    public function getCommand(): array
    {
        return ['account', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'title' => $this->title,
            'account' => $this->accountHash,
            'auth_code' => $this->authCode,
        ];
    }

    /**
     * @param \M2E\Core\Model\Connector\Response $response
     *
     * @return \M2E\Otto\Model\Otto\Connector\Account\Update\Response
     * @throws \M2E\Otto\Model\Account\Exception\InstallNotFound
     */
    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Otto\Model\Otto\Connector\Account\Update\Response {
        if ($this->hasErrorInstallNotFound($response->getMessageCollection())) {
            throw new \M2E\Otto\Model\Account\Exception\InstallNotFound('Installation not found.');
        }

        $responseData = $response->getResponseData();

        $installationId = $responseData['account']['installation_id'];

        return new \M2E\Otto\Model\Otto\Connector\Account\Update\Response(
            $installationId
        );
    }

    private function hasErrorInstallNotFound(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): bool {
        foreach ($messageCollection->getErrors() as $error) {
            if ((int)$error->getCode() === self::ERROR_CODE_INSTALL_NOT_FOUND) {
                return true;
            }
        }

        return false;
    }
}
