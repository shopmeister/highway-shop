<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\Installation;

use Doctrine\DBAL\Connection;
use Pickware\DalBundle\DefaultTranslationProvider;
use Pickware\DalBundle\EntityManager;
use Pickware\InstallationLibrary\CustomFieldSet\CustomFieldSetInstaller;
use Pickware\InstallationLibrary\MailTemplate\MailTemplateInstaller;
use Pickware\InstallationLibrary\MailTemplate\MailTemplateUninstaller;
use Pickware\PickwareDhl\PreferredDelivery\PreferredDeliveryCustomFieldSet;
use Pickware\PickwareDhl\ReturnLabel\ReturnLabelMailTemplate;
use Pickware\ShippingBundle\Installation\CarrierInstaller;
use Pickware\ShippingBundle\Installation\CarrierUninstaller;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Exclude]
class PickwareDhlInstaller
{
    private MailTemplateInstaller $mailTemplateInstaller;
    private MailTemplateUninstaller $mailTemplateUninstaller;
    private CarrierInstaller $carrierInstaller;
    private CarrierUninstaller $carrierUninstaller;
    private CustomFieldSetInstaller $customFieldSetInstaller;

    private function __construct()
    {
        // Create an instance with ::initFromContainer()
    }

    public static function initFromContainer(ContainerInterface $container): self
    {
        $self = new self();
        /** @var Connection $db */
        $db = $container->get(Connection::class);
        $defaultTranslationProvider = new DefaultTranslationProvider($container, $db);
        $entityManager = new EntityManager($container, $db, $defaultTranslationProvider, new EntityDefinitionQueryHelper());
        $self->mailTemplateInstaller = new MailTemplateInstaller($entityManager);
        $self->mailTemplateUninstaller = new MailTemplateUninstaller($entityManager);
        $self->carrierInstaller = new CarrierInstaller($db);
        $self->carrierUninstaller = CarrierUninstaller::createForContainer($container);
        $self->customFieldSetInstaller = new CustomFieldSetInstaller($entityManager);

        return $self;
    }

    public function postInstall(InstallContext $installContext): void
    {
        $this->install($installContext->getContext());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        $this->install($updateContext->getContext());
    }

    private function install(Context $context): void
    {
        $this->mailTemplateInstaller->installMailTemplate(
            new ReturnLabelMailTemplate(),
            $context,
        );

        // The carrier will reference the mail template type, therefore create them after the mail templates
        $this->carrierInstaller->installCarrier(new DhlCarrier());

        $this->customFieldSetInstaller->installCustomFieldSet(
            new PreferredDeliveryCustomFieldSet(),
            $context,
        );
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $this->carrierUninstaller->uninstallCarrier(DhlCarrier::TECHNICAL_NAME);
        $this->mailTemplateUninstaller->uninstallMailTemplate(new ReturnLabelMailTemplate(), $uninstallContext->getContext());
    }
}
