<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Model Class Map
    |--------------------------------------------------------------------------
    |
    | Maps model keys to their fully-qualified class names. Host applications
    | can override any model by publishing this config and pointing to their
    | own model class. The 'user' model defaults to the host app's User model.
    |
    */

    'models' => [
        'user'                    => \App\Models\User::class,
        'admin_setting'           => \Appsolutely\AIO\Models\AdminSetting::class,
        'article'                 => \Appsolutely\AIO\Models\Article::class,
        'article_category'        => \Appsolutely\AIO\Models\ArticleCategory::class,
        'cart'                    => \Appsolutely\AIO\Models\Cart::class,
        'cart_item'               => \Appsolutely\AIO\Models\CartItem::class,
        'coupon'                  => \Appsolutely\AIO\Models\Coupon::class,
        'coupon_usage'            => \Appsolutely\AIO\Models\CouponUsage::class,
        'delivery_token'          => \Appsolutely\AIO\Models\DeliveryToken::class,
        'file'                    => \Appsolutely\AIO\Models\File::class,
        'file_attachment'         => \Appsolutely\AIO\Models\FileAttachment::class,
        'form'                    => \Appsolutely\AIO\Models\Form::class,
        'form_entry'              => \Appsolutely\AIO\Models\FormEntry::class,
        'form_field'              => \Appsolutely\AIO\Models\FormField::class,
        'general_page'            => \Appsolutely\AIO\Models\GeneralPage::class,
        'inventory_movement'      => \Appsolutely\AIO\Models\InventoryMovement::class,
        'membership'              => \Appsolutely\AIO\Models\Membership::class,
        'menu'                    => \Appsolutely\AIO\Models\CmsMenu::class,
        'notification_queue'      => \Appsolutely\AIO\Models\NotificationQueue::class,
        'notification_rule'       => \Appsolutely\AIO\Models\NotificationRule::class,
        'notification_sender'     => \Appsolutely\AIO\Models\NotificationSender::class,
        'notification_template'   => \Appsolutely\AIO\Models\NotificationTemplate::class,
        'order'                   => \Appsolutely\AIO\Models\Order::class,
        'order_item'              => \Appsolutely\AIO\Models\OrderItem::class,
        'order_payment'           => \Appsolutely\AIO\Models\OrderPayment::class,
        'order_shipment'          => \Appsolutely\AIO\Models\OrderShipment::class,
        'order_status_history'    => \Appsolutely\AIO\Models\OrderStatusHistory::class,
        'page'                    => \Appsolutely\AIO\Models\Page::class,
        'page_block'              => \Appsolutely\AIO\Models\PageBlock::class,
        'page_block_group'        => \Appsolutely\AIO\Models\PageBlockGroup::class,
        'page_block_setting'      => \Appsolutely\AIO\Models\PageBlockSetting::class,
        'page_block_value'        => \Appsolutely\AIO\Models\PageBlockValue::class,
        'payment'                 => \Appsolutely\AIO\Models\Payment::class,
        'product'                 => \Appsolutely\AIO\Models\Product::class,
        'product_attribute'       => \Appsolutely\AIO\Models\ProductAttribute::class,
        'product_attribute_group' => \Appsolutely\AIO\Models\ProductAttributeGroup::class,
        'product_attribute_value' => \Appsolutely\AIO\Models\ProductAttributeValue::class,
        'product_category'        => \Appsolutely\AIO\Models\ProductCategory::class,
        'product_image'           => \Appsolutely\AIO\Models\ProductImage::class,
        'product_review'          => \Appsolutely\AIO\Models\ProductReview::class,
        'product_sku'             => \Appsolutely\AIO\Models\ProductSku::class,
        'refund'                  => \Appsolutely\AIO\Models\Refund::class,
        'release_build'           => \Appsolutely\AIO\Models\ReleaseBuild::class,
        'release_version'         => \Appsolutely\AIO\Models\ReleaseVersion::class,
        'shipping_rate'           => \Appsolutely\AIO\Models\ShippingRate::class,
        'shipping_zone'           => \Appsolutely\AIO\Models\ShippingZone::class,
        'tax_rate'                => \Appsolutely\AIO\Models\TaxRate::class,
        'team'                    => \App\Models\Team::class,
        'team_invitation'         => \App\Models\TeamInvitation::class,
        'translation'             => \Appsolutely\AIO\Models\Translation::class,
        'user_address'            => \Appsolutely\AIO\Models\UserAddress::class,
        'wishlist'                => \Appsolutely\AIO\Models\Wishlist::class,
        'wishlist_item'           => \Appsolutely\AIO\Models\WishlistItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Bindings
    |--------------------------------------------------------------------------
    |
    | Maps service interfaces to their implementations. All services are
    | registered as singletons. Host applications can override any service
    | by rebinding the interface in their own service provider.
    |
    */

    'services' => [
        \Appsolutely\AIO\Services\Contracts\ArticleServiceInterface::class            => \Appsolutely\AIO\Services\ArticleService::class,
        \Appsolutely\AIO\Services\Contracts\BlockRendererServiceInterface::class       => \Appsolutely\AIO\Services\BlockRendererService::class,
        \Appsolutely\AIO\Services\Contracts\CartServiceInterface::class                => \Appsolutely\AIO\Services\CartService::class,
        \Appsolutely\AIO\Services\Contracts\CouponServiceInterface::class              => \Appsolutely\AIO\Services\CouponService::class,
        \Appsolutely\AIO\Services\Contracts\DeliveryServiceInterface::class            => \Appsolutely\AIO\Services\DeliveryService::class,
        \Appsolutely\AIO\Services\Contracts\DynamicFormExportServiceInterface::class   => \Appsolutely\AIO\Services\DynamicFormExportService::class,
        \Appsolutely\AIO\Services\Contracts\DynamicFormRenderServiceInterface::class   => \Appsolutely\AIO\Services\DynamicFormRenderService::class,
        \Appsolutely\AIO\Services\Contracts\DynamicFormServiceInterface::class         => \Appsolutely\AIO\Services\DynamicFormService::class,
        \Appsolutely\AIO\Services\Contracts\DynamicFormSubmissionServiceInterface::class => \Appsolutely\AIO\Services\DynamicFormSubmissionService::class,
        \Appsolutely\AIO\Services\Contracts\DynamicFormValidationServiceInterface::class => \Appsolutely\AIO\Services\DynamicFormValidationService::class,
        \Appsolutely\AIO\Services\Contracts\FormEntriesPullServiceInterface::class     => \Appsolutely\AIO\Services\FormEntriesPullService::class,
        \Appsolutely\AIO\Services\Contracts\FormExportServiceInterface::class          => \Appsolutely\AIO\Services\FormExportService::class,
        \Appsolutely\AIO\Services\Contracts\GeneralPageServiceInterface::class         => \Appsolutely\AIO\Services\GeneralPageService::class,
        \Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface::class   => \Appsolutely\AIO\Services\ImageOptimizationService::class,
        \Appsolutely\AIO\Services\Contracts\InventoryServiceInterface::class           => \Appsolutely\AIO\Services\InventoryService::class,
        \Appsolutely\AIO\Services\Contracts\ManifestServiceInterface::class            => \Appsolutely\AIO\Services\ManifestService::class,
        \Appsolutely\AIO\Services\Contracts\MenuServiceInterface::class                => \Appsolutely\AIO\Services\MenuService::class,
        \Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface::class   => \Appsolutely\AIO\Services\NotificationQueueService::class,
        \Appsolutely\AIO\Services\Contracts\NotificationRuleServiceInterface::class    => \Appsolutely\AIO\Services\NotificationRuleService::class,
        \Appsolutely\AIO\Services\Contracts\NotificationServiceInterface::class        => \Appsolutely\AIO\Services\NotificationService::class,
        \Appsolutely\AIO\Services\Contracts\NotificationTemplateServiceInterface::class => \Appsolutely\AIO\Services\NotificationTemplateService::class,
        \Appsolutely\AIO\Services\Contracts\OrderServiceInterface::class               => \Appsolutely\AIO\Services\OrderService::class,
        \Appsolutely\AIO\Services\Contracts\OrderShipmentServiceInterface::class       => \Appsolutely\AIO\Services\OrderShipmentService::class,
        \Appsolutely\AIO\Services\Contracts\OrderStatusHistoryServiceInterface::class  => \Appsolutely\AIO\Services\OrderStatusHistoryService::class,
        \Appsolutely\AIO\Services\Contracts\PageBlockSchemaServiceInterface::class     => \Appsolutely\AIO\Services\PageBlockSchemaService::class,
        \Appsolutely\AIO\Services\Contracts\PageBlockServiceInterface::class           => \Appsolutely\AIO\Services\PageBlockService::class,
        \Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface::class    => \Appsolutely\AIO\Services\PageBlockSettingService::class,
        \Appsolutely\AIO\Services\Contracts\PageServiceInterface::class                => \Appsolutely\AIO\Services\PageService::class,
        \Appsolutely\AIO\Services\Contracts\PageStructureServiceInterface::class       => \Appsolutely\AIO\Services\PageStructureService::class,
        \Appsolutely\AIO\Services\Contracts\PaymentServiceInterface::class             => \Appsolutely\AIO\Services\PaymentService::class,
        \Appsolutely\AIO\Services\Contracts\ProductAttributeServiceInterface::class    => \Appsolutely\AIO\Services\ProductAttributeService::class,
        \Appsolutely\AIO\Services\Contracts\ProductImageServiceInterface::class        => \Appsolutely\AIO\Services\ProductImageService::class,
        \Appsolutely\AIO\Services\Contracts\ProductReviewServiceInterface::class       => \Appsolutely\AIO\Services\ProductReviewService::class,
        \Appsolutely\AIO\Services\Contracts\ProductServiceInterface::class             => \Appsolutely\AIO\Services\ProductService::class,
        \Appsolutely\AIO\Services\Contracts\RefundServiceInterface::class              => \Appsolutely\AIO\Services\RefundService::class,
        \Appsolutely\AIO\Services\Contracts\ReleaseServiceInterface::class             => \Appsolutely\AIO\Services\ReleaseService::class,
        \Appsolutely\AIO\Services\Contracts\RouteRestrictionServiceInterface::class    => \Appsolutely\AIO\Services\RouteRestrictionService::class,
        \Appsolutely\AIO\Services\Contracts\ShippingServiceInterface::class            => \Appsolutely\AIO\Services\ShippingService::class,
        \Appsolutely\AIO\Services\Contracts\SitemapServiceInterface::class             => \Appsolutely\AIO\Services\SitemapService::class,
        \Appsolutely\AIO\Services\Contracts\StorageServiceInterface::class             => \Appsolutely\AIO\Services\StorageService::class,
        \Appsolutely\AIO\Services\Contracts\TaxServiceInterface::class                 => \Appsolutely\AIO\Services\TaxService::class,
        \Appsolutely\AIO\Services\Contracts\ThemeServiceInterface::class               => \Appsolutely\AIO\Services\ThemeService::class,
        \Appsolutely\AIO\Services\Contracts\TranslationServiceInterface::class         => \Appsolutely\AIO\Services\TranslationService::class,
        \Appsolutely\AIO\Services\Contracts\UserAddressServiceInterface::class         => \Appsolutely\AIO\Services\UserAddressService::class,
        \Appsolutely\AIO\Services\Contracts\WishlistServiceInterface::class            => \Appsolutely\AIO\Services\WishlistService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository Bindings
    |--------------------------------------------------------------------------
    |
    | Maps repository keys to their fully-qualified class names. Used by the
    | AIOServiceProvider to register repository singletons.
    |
    */

    'repositories' => [
        'admin_setting'           => \Appsolutely\AIO\Repositories\AdminSettingRepository::class,
        'article'                 => \Appsolutely\AIO\Repositories\ArticleRepository::class,
        'article_category'        => \Appsolutely\AIO\Repositories\ArticleCategoryRepository::class,
        'cart'                    => \Appsolutely\AIO\Repositories\CartRepository::class,
        'cart_item'               => \Appsolutely\AIO\Repositories\CartItemRepository::class,
        'coupon'                  => \Appsolutely\AIO\Repositories\CouponRepository::class,
        'coupon_usage'            => \Appsolutely\AIO\Repositories\CouponUsageRepository::class,
        'delivery_token'          => \Appsolutely\AIO\Repositories\DeliveryTokenRepository::class,
        'file'                    => \Appsolutely\AIO\Repositories\FileRepository::class,
        'form'                    => \Appsolutely\AIO\Repositories\FormRepository::class,
        'form_entry'              => \Appsolutely\AIO\Repositories\FormEntryRepository::class,
        'form_field'              => \Appsolutely\AIO\Repositories\FormFieldRepository::class,
        'inventory_movement'      => \Appsolutely\AIO\Repositories\InventoryMovementRepository::class,
        'menu'                    => \Appsolutely\AIO\Repositories\MenuRepository::class,
        'notification_queue'      => \Appsolutely\AIO\Repositories\NotificationQueueRepository::class,
        'notification_rule'       => \Appsolutely\AIO\Repositories\NotificationRuleRepository::class,
        'notification_sender'     => \Appsolutely\AIO\Repositories\NotificationSenderRepository::class,
        'notification_template'   => \Appsolutely\AIO\Repositories\NotificationTemplateRepository::class,
        'order'                   => \Appsolutely\AIO\Repositories\OrderRepository::class,
        'order_item'              => \Appsolutely\AIO\Repositories\OrderItemRepository::class,
        'order_payment'           => \Appsolutely\AIO\Repositories\OrderPaymentRepository::class,
        'order_shipment'          => \Appsolutely\AIO\Repositories\OrderShipmentRepository::class,
        'order_status_history'    => \Appsolutely\AIO\Repositories\OrderStatusHistoryRepository::class,
        'page'                    => \Appsolutely\AIO\Repositories\PageRepository::class,
        'page_block'              => \Appsolutely\AIO\Repositories\PageBlockRepository::class,
        'page_block_group'        => \Appsolutely\AIO\Repositories\PageBlockGroupRepository::class,
        'page_block_setting'      => \Appsolutely\AIO\Repositories\PageBlockSettingRepository::class,
        'page_block_value'        => \Appsolutely\AIO\Repositories\PageBlockValueRepository::class,
        'payment'                 => \Appsolutely\AIO\Repositories\PaymentRepository::class,
        'product'                 => \Appsolutely\AIO\Repositories\ProductRepository::class,
        'product_attribute'       => \Appsolutely\AIO\Repositories\ProductAttributeRepository::class,
        'product_attribute_group' => \Appsolutely\AIO\Repositories\ProductAttributeGroupRepository::class,
        'product_attribute_value' => \Appsolutely\AIO\Repositories\ProductAttributeValueRepository::class,
        'product_category'        => \Appsolutely\AIO\Repositories\ProductCategoryRepository::class,
        'product_image'           => \Appsolutely\AIO\Repositories\ProductImageRepository::class,
        'product_review'          => \Appsolutely\AIO\Repositories\ProductReviewRepository::class,
        'product_sku'             => \Appsolutely\AIO\Repositories\ProductSkuRepository::class,
        'refund'                  => \Appsolutely\AIO\Repositories\RefundRepository::class,
        'release_build'           => \Appsolutely\AIO\Repositories\ReleaseBuildRepository::class,
        'release_version'         => \Appsolutely\AIO\Repositories\ReleaseVersionRepository::class,
        'shipping_rate'           => \Appsolutely\AIO\Repositories\ShippingRateRepository::class,
        'shipping_zone'           => \Appsolutely\AIO\Repositories\ShippingZoneRepository::class,
        'tax_rate'                => \Appsolutely\AIO\Repositories\TaxRateRepository::class,
        'translation'             => \Appsolutely\AIO\Repositories\TranslationRepository::class,
        'user'                    => \Appsolutely\AIO\Repositories\UserRepository::class,
        'user_address'            => \Appsolutely\AIO\Repositories\UserAddressRepository::class,
        'wishlist'                => \Appsolutely\AIO\Repositories\WishlistRepository::class,
        'wishlist_item'           => \Appsolutely\AIO\Repositories\WishlistItemRepository::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Registration
    |--------------------------------------------------------------------------
    |
    | Toggle automatic route registration. Set to false to disable AIO's
    | default routes and define your own in the host application.
    |
    */

    'routes' => [
        'web'   => true,
        'api'   => true,
        'admin' => true,
        'cache' => true,
    ],

];
