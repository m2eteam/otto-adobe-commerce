<?php

declare(strict_types=1);

namespace M2E\Otto\Setup;

class UpgradeCollection extends \M2E\Core\Model\Setup\AbstractUpgradeCollection
{
    public function getMinAllowedVersion(): string
    {
        return '1.0.0';
    }

    protected function getSourceVersionUpgrades(): array
    {
        return [
            '1.0.0' => ['to' => '1.0.1', 'upgrade' => null],
            '1.0.1' => ['to' => '1.0.2', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_0_2\Config::class],
            '1.0.2' => ['to' => '1.0.3', 'upgrade' => null],
            '1.0.3' => ['to' => '1.1.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_1_0\Config::class],
            '1.1.0' => ['to' => '1.1.1', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_1_1\Config::class],
            '1.1.1' => ['to' => '1.2.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_2_0\Config::class],
            '1.2.0' => ['to' => '1.2.1', 'upgrade' => null],
            '1.2.1' => ['to' => '1.3.0', 'upgrade' => null],
            '1.3.0' => ['to' => '1.3.1', 'upgrade' => null],
            '1.3.1' => ['to' => '1.4.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_4_0\Config::class],
            '1.4.0' => ['to' => '1.4.1', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_4_1\Config::class],
            '1.4.1' => ['to' => '1.5.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_5_0\Config::class],
            '1.5.0' => ['to' => '1.5.1', 'upgrade' => null],
            '1.5.1' => ['to' => '1.5.2', 'upgrade' => null],
            '1.5.2' => ['to' => '1.5.3', 'upgrade' => null],
            '1.5.3' => ['to' => '1.6.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_6_0\Config::class],
            '1.6.0' => ['to' => '1.6.1', 'upgrade' => null],
            '1.6.1' => ['to' => '1.7.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_7_0\Config::class],
            '1.7.0' => ['to' => '1.8.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_8_0\Config::class],
            '1.8.0' => ['to' => '1.8.1', 'upgrade' => null],
            '1.8.1' => ['to' => '2.0.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_0_0\Config::class],
            '2.0.0' => ['to' => '2.0.1', 'upgrade' => null],
            '2.0.1' => ['to' => '2.0.2', 'upgrade' => null],
            '2.0.2' => ['to' => '2.0.3', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_0_3\Config::class],
            '2.0.3' => ['to' => '2.0.4', 'upgrade' => null],
            '2.0.4' => ['to' => '2.1.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_1_0\Config::class],
            '2.1.0' => ['to' => '2.2.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_2_0\Config::class],
            '2.2.0' => ['to' => '2.2.1', 'upgrade' => null],
            '2.2.1' => ['to' => '2.3.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_3_0\Config::class],
            '2.3.0' => ['to' => '2.4.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_4_0\Config::class],
            '2.4.0' => ['to' => '2.4.1', 'upgrade' => null],
            '2.4.1' => ['to' => '2.5.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_5_0\Config::class],
            '2.5.0' => ['to' => '2.6.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_6_0\Config::class],
            '2.6.0' => ['to' => '2.7.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v2_7_0\Config::class],
        ];
    }
}
