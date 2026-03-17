<?php

declare(strict_types=1);
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Appsolutely\AIO\Models\AdminSetting;
use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\ArticleCategory;
use Appsolutely\AIO\Models\Cart;
use Appsolutely\AIO\Models\CartItem;
use Appsolutely\AIO\Models\CmsMenu;
use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\CouponUsage;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\InventoryMovement;
use Appsolutely\AIO\Models\Membership;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Models\OrderStatusHistory;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Models\PageBlockGroup;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductAttribute;
use Appsolutely\AIO\Models\ProductAttributeGroup;
use Appsolutely\AIO\Models\ProductAttributeValue;
use Appsolutely\AIO\Models\ProductCategory;
use Appsolutely\AIO\Models\ProductImage;
use Appsolutely\AIO\Models\ProductReview;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Models\ReleaseVersion;
use Appsolutely\AIO\Models\ShippingRate;
use Appsolutely\AIO\Models\ShippingZone;
use Appsolutely\AIO\Models\TaxRate;
use Appsolutely\AIO\Models\Translation;
use Appsolutely\AIO\Models\UserAddress;
use Appsolutely\AIO\Models\Wishlist;
use Appsolutely\AIO\Models\WishlistItem;
use Appsolutely\AIO\Repositories\AdminSettingRepository;
use Appsolutely\AIO\Repositories\ArticleCategoryRepository;
use Appsolutely\AIO\Repositories\ArticleRepository;
use Appsolutely\AIO\Repositories\CartItemRepository;
use Appsolutely\AIO\Repositories\CartRepository;
use Appsolutely\AIO\Repositories\CouponRepository;
use Appsolutely\AIO\Repositories\CouponUsageRepository;
use Appsolutely\AIO\Repositories\DeliveryTokenRepository;
use Appsolutely\AIO\Repositories\FileRepository;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormFieldRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Repositories\InventoryMovementRepository;
use Appsolutely\AIO\Repositories\MenuRepository;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Appsolutely\AIO\Repositories\OrderItemRepository;
use Appsolutely\AIO\Repositories\OrderPaymentRepository;
use Appsolutely\AIO\Repositories\OrderRepository;
use Appsolutely\AIO\Repositories\OrderShipmentRepository;
use Appsolutely\AIO\Repositories\OrderStatusHistoryRepository;
use Appsolutely\AIO\Repositories\PageBlockGroupRepository;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Repositories\PageBlockValueRepository;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Repositories\PaymentRepository;
use Appsolutely\AIO\Repositories\ProductAttributeGroupRepository;
use Appsolutely\AIO\Repositories\ProductAttributeRepository;
use Appsolutely\AIO\Repositories\ProductAttributeValueRepository;
use Appsolutely\AIO\Repositories\ProductCategoryRepository;
use Appsolutely\AIO\Repositories\ProductImageRepository;
use Appsolutely\AIO\Repositories\ProductRepository;
use Appsolutely\AIO\Repositories\ProductReviewRepository;
use Appsolutely\AIO\Repositories\ProductSkuRepository;
use Appsolutely\AIO\Repositories\RefundRepository;
use Appsolutely\AIO\Repositories\ReleaseBuildRepository;
use Appsolutely\AIO\Repositories\ReleaseVersionRepository;
use Appsolutely\AIO\Repositories\ShippingRateRepository;
use Appsolutely\AIO\Repositories\ShippingZoneRepository;
use Appsolutely\AIO\Repositories\TaxRateRepository;
use Appsolutely\AIO\Repositories\TranslationRepository;
use Appsolutely\AIO\Repositories\UserAddressRepository;
use Appsolutely\AIO\Repositories\UserRepository;
use Appsolutely\AIO\Repositories\WishlistItemRepository;
use Appsolutely\AIO\Repositories\WishlistRepository;
use Appsolutely\AIO\Services\ArticleService;
use Appsolutely\AIO\Services\BlockRendererService;
use Appsolutely\AIO\Services\CartService;
use Appsolutely\AIO\Services\Contracts\ArticleServiceInterface;
use Appsolutely\AIO\Services\Contracts\BlockRendererServiceInterface;
use Appsolutely\AIO\Services\Contracts\CartServiceInterface;
use Appsolutely\AIO\Services\Contracts\CouponServiceInterface;
use Appsolutely\AIO\Services\Contracts\DeliveryServiceInterface;
use Appsolutely\AIO\Services\Contracts\DynamicFormExportServiceInterface;
use Appsolutely\AIO\Services\Contracts\DynamicFormRenderServiceInterface;
use Appsolutely\AIO\Services\Contracts\DynamicFormServiceInterface;
use Appsolutely\AIO\Services\Contracts\DynamicFormSubmissionServiceInterface;
use Appsolutely\AIO\Services\Contracts\DynamicFormValidationServiceInterface;
use Appsolutely\AIO\Services\Contracts\FormEntriesPullServiceInterface;
use Appsolutely\AIO\Services\Contracts\FormExportServiceInterface;
use Appsolutely\AIO\Services\Contracts\GeneralPageServiceInterface;
use Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface;
use Appsolutely\AIO\Services\Contracts\InventoryServiceInterface;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Appsolutely\AIO\Services\Contracts\MenuServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationRuleServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationTemplateServiceInterface;
use Appsolutely\AIO\Services\Contracts\OrderServiceInterface;
use Appsolutely\AIO\Services\Contracts\OrderShipmentServiceInterface;
use Appsolutely\AIO\Services\Contracts\OrderStatusHistoryServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockSchemaServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageStructureServiceInterface;
use Appsolutely\AIO\Services\Contracts\PaymentServiceInterface;
use Appsolutely\AIO\Services\Contracts\ProductAttributeServiceInterface;
use Appsolutely\AIO\Services\Contracts\ProductImageServiceInterface;
use Appsolutely\AIO\Services\Contracts\ProductReviewServiceInterface;
use Appsolutely\AIO\Services\Contracts\ProductServiceInterface;
use Appsolutely\AIO\Services\Contracts\RefundServiceInterface;
use Appsolutely\AIO\Services\Contracts\ReleaseServiceInterface;
use Appsolutely\AIO\Services\Contracts\RouteRestrictionServiceInterface;
use Appsolutely\AIO\Services\Contracts\ShippingServiceInterface;
use Appsolutely\AIO\Services\Contracts\SitemapServiceInterface;
use Appsolutely\AIO\Services\Contracts\StorageServiceInterface;
use Appsolutely\AIO\Services\Contracts\TaxServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\Contracts\TranslationServiceInterface;
use Appsolutely\AIO\Services\Contracts\UserAddressServiceInterface;
use Appsolutely\AIO\Services\Contracts\WishlistServiceInterface;
use Appsolutely\AIO\Services\CouponService;
use Appsolutely\AIO\Services\DeliveryService;
use Appsolutely\AIO\Services\DynamicFormExportService;
use Appsolutely\AIO\Services\DynamicFormRenderService;
use Appsolutely\AIO\Services\DynamicFormService;
use Appsolutely\AIO\Services\DynamicFormSubmissionService;
use Appsolutely\AIO\Services\DynamicFormValidationService;
use Appsolutely\AIO\Services\FormEntriesPullService;
use Appsolutely\AIO\Services\FormExportService;
use Appsolutely\AIO\Services\GeneralPageService;
use Appsolutely\AIO\Services\ImageOptimizationService;
use Appsolutely\AIO\Services\InventoryService;
use Appsolutely\AIO\Services\ManifestService;
use Appsolutely\AIO\Services\MenuService;
use Appsolutely\AIO\Services\NotificationQueueService;
use Appsolutely\AIO\Services\NotificationRuleService;
use Appsolutely\AIO\Services\NotificationService;
use Appsolutely\AIO\Services\NotificationTemplateService;
use Appsolutely\AIO\Services\OrderService;
use Appsolutely\AIO\Services\OrderShipmentService;
use Appsolutely\AIO\Services\OrderStatusHistoryService;
use Appsolutely\AIO\Services\PageBlockSchemaService;
use Appsolutely\AIO\Services\PageBlockService;
use Appsolutely\AIO\Services\PageBlockSettingService;
use Appsolutely\AIO\Services\PageService;
use Appsolutely\AIO\Services\PageStructureService;
use Appsolutely\AIO\Services\PaymentService;
use Appsolutely\AIO\Services\ProductAttributeService;
use Appsolutely\AIO\Services\ProductImageService;
use Appsolutely\AIO\Services\ProductReviewService;
use Appsolutely\AIO\Services\ProductService;
use Appsolutely\AIO\Services\RefundService;
use Appsolutely\AIO\Services\ReleaseService;
use Appsolutely\AIO\Services\RouteRestrictionService;
use Appsolutely\AIO\Services\ShippingService;
use Appsolutely\AIO\Services\SitemapService;
use Appsolutely\AIO\Services\StorageService;
use Appsolutely\AIO\Services\TaxService;
use Appsolutely\AIO\Services\ThemeService;
use Appsolutely\AIO\Services\TranslationService;
use Appsolutely\AIO\Services\UserAddressService;
use Appsolutely\AIO\Services\WishlistService;

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
        'user'                    => User::class,
        'admin_setting'           => AdminSetting::class,
        'article'                 => Article::class,
        'article_category'        => ArticleCategory::class,
        'cart'                    => Cart::class,
        'cart_item'               => CartItem::class,
        'coupon'                  => Coupon::class,
        'coupon_usage'            => CouponUsage::class,
        'delivery_token'          => DeliveryToken::class,
        'file'                    => File::class,
        'file_attachment'         => FileAttachment::class,
        'form'                    => Form::class,
        'form_entry'              => FormEntry::class,
        'form_field'              => FormField::class,
        'general_page'            => GeneralPage::class,
        'inventory_movement'      => InventoryMovement::class,
        'membership'              => Membership::class,
        'menu'                    => CmsMenu::class,
        'notification_queue'      => NotificationQueue::class,
        'notification_rule'       => NotificationRule::class,
        'notification_sender'     => NotificationSender::class,
        'notification_template'   => NotificationTemplate::class,
        'order'                   => Order::class,
        'order_item'              => OrderItem::class,
        'order_payment'           => OrderPayment::class,
        'order_shipment'          => OrderShipment::class,
        'order_status_history'    => OrderStatusHistory::class,
        'page'                    => Page::class,
        'page_block'              => PageBlock::class,
        'page_block_group'        => PageBlockGroup::class,
        'page_block_setting'      => PageBlockSetting::class,
        'page_block_value'        => PageBlockValue::class,
        'payment'                 => Payment::class,
        'product'                 => Product::class,
        'product_attribute'       => ProductAttribute::class,
        'product_attribute_group' => ProductAttributeGroup::class,
        'product_attribute_value' => ProductAttributeValue::class,
        'product_category'        => ProductCategory::class,
        'product_image'           => ProductImage::class,
        'product_review'          => ProductReview::class,
        'product_sku'             => ProductSku::class,
        'refund'                  => Refund::class,
        'release_build'           => ReleaseBuild::class,
        'release_version'         => ReleaseVersion::class,
        'shipping_rate'           => ShippingRate::class,
        'shipping_zone'           => ShippingZone::class,
        'tax_rate'                => TaxRate::class,
        'team'                    => Team::class,
        'team_invitation'         => TeamInvitation::class,
        'translation'             => Translation::class,
        'user_address'            => UserAddress::class,
        'wishlist'                => Wishlist::class,
        'wishlist_item'           => WishlistItem::class,
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
        ArticleServiceInterface::class               => ArticleService::class,
        BlockRendererServiceInterface::class         => BlockRendererService::class,
        CartServiceInterface::class                  => CartService::class,
        CouponServiceInterface::class                => CouponService::class,
        DeliveryServiceInterface::class              => DeliveryService::class,
        DynamicFormExportServiceInterface::class     => DynamicFormExportService::class,
        DynamicFormRenderServiceInterface::class     => DynamicFormRenderService::class,
        DynamicFormServiceInterface::class           => DynamicFormService::class,
        DynamicFormSubmissionServiceInterface::class => DynamicFormSubmissionService::class,
        DynamicFormValidationServiceInterface::class => DynamicFormValidationService::class,
        FormEntriesPullServiceInterface::class       => FormEntriesPullService::class,
        FormExportServiceInterface::class            => FormExportService::class,
        GeneralPageServiceInterface::class           => GeneralPageService::class,
        ImageOptimizationServiceInterface::class     => ImageOptimizationService::class,
        InventoryServiceInterface::class             => InventoryService::class,
        ManifestServiceInterface::class              => ManifestService::class,
        MenuServiceInterface::class                  => MenuService::class,
        NotificationQueueServiceInterface::class     => NotificationQueueService::class,
        NotificationRuleServiceInterface::class      => NotificationRuleService::class,
        NotificationServiceInterface::class          => NotificationService::class,
        NotificationTemplateServiceInterface::class  => NotificationTemplateService::class,
        OrderServiceInterface::class                 => OrderService::class,
        OrderShipmentServiceInterface::class         => OrderShipmentService::class,
        OrderStatusHistoryServiceInterface::class    => OrderStatusHistoryService::class,
        PageBlockSchemaServiceInterface::class       => PageBlockSchemaService::class,
        PageBlockServiceInterface::class             => PageBlockService::class,
        PageBlockSettingServiceInterface::class      => PageBlockSettingService::class,
        PageServiceInterface::class                  => PageService::class,
        PageStructureServiceInterface::class         => PageStructureService::class,
        PaymentServiceInterface::class               => PaymentService::class,
        ProductAttributeServiceInterface::class      => ProductAttributeService::class,
        ProductImageServiceInterface::class          => ProductImageService::class,
        ProductReviewServiceInterface::class         => ProductReviewService::class,
        ProductServiceInterface::class               => ProductService::class,
        RefundServiceInterface::class                => RefundService::class,
        ReleaseServiceInterface::class               => ReleaseService::class,
        RouteRestrictionServiceInterface::class      => RouteRestrictionService::class,
        ShippingServiceInterface::class              => ShippingService::class,
        SitemapServiceInterface::class               => SitemapService::class,
        StorageServiceInterface::class               => StorageService::class,
        TaxServiceInterface::class                   => TaxService::class,
        ThemeServiceInterface::class                 => ThemeService::class,
        TranslationServiceInterface::class           => TranslationService::class,
        UserAddressServiceInterface::class           => UserAddressService::class,
        WishlistServiceInterface::class              => WishlistService::class,
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
        'admin_setting'           => AdminSettingRepository::class,
        'article'                 => ArticleRepository::class,
        'article_category'        => ArticleCategoryRepository::class,
        'cart'                    => CartRepository::class,
        'cart_item'               => CartItemRepository::class,
        'coupon'                  => CouponRepository::class,
        'coupon_usage'            => CouponUsageRepository::class,
        'delivery_token'          => DeliveryTokenRepository::class,
        'file'                    => FileRepository::class,
        'form'                    => FormRepository::class,
        'form_entry'              => FormEntryRepository::class,
        'form_field'              => FormFieldRepository::class,
        'inventory_movement'      => InventoryMovementRepository::class,
        'menu'                    => MenuRepository::class,
        'notification_queue'      => NotificationQueueRepository::class,
        'notification_rule'       => NotificationRuleRepository::class,
        'notification_sender'     => NotificationSenderRepository::class,
        'notification_template'   => NotificationTemplateRepository::class,
        'order'                   => OrderRepository::class,
        'order_item'              => OrderItemRepository::class,
        'order_payment'           => OrderPaymentRepository::class,
        'order_shipment'          => OrderShipmentRepository::class,
        'order_status_history'    => OrderStatusHistoryRepository::class,
        'page'                    => PageRepository::class,
        'page_block'              => PageBlockRepository::class,
        'page_block_group'        => PageBlockGroupRepository::class,
        'page_block_setting'      => PageBlockSettingRepository::class,
        'page_block_value'        => PageBlockValueRepository::class,
        'payment'                 => PaymentRepository::class,
        'product'                 => ProductRepository::class,
        'product_attribute'       => ProductAttributeRepository::class,
        'product_attribute_group' => ProductAttributeGroupRepository::class,
        'product_attribute_value' => ProductAttributeValueRepository::class,
        'product_category'        => ProductCategoryRepository::class,
        'product_image'           => ProductImageRepository::class,
        'product_review'          => ProductReviewRepository::class,
        'product_sku'             => ProductSkuRepository::class,
        'refund'                  => RefundRepository::class,
        'release_build'           => ReleaseBuildRepository::class,
        'release_version'         => ReleaseVersionRepository::class,
        'shipping_rate'           => ShippingRateRepository::class,
        'shipping_zone'           => ShippingZoneRepository::class,
        'tax_rate'                => TaxRateRepository::class,
        'translation'             => TranslationRepository::class,
        'user'                    => UserRepository::class,
        'user_address'            => UserAddressRepository::class,
        'wishlist'                => WishlistRepository::class,
        'wishlist_item'           => WishlistItemRepository::class,
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

        /*
        |----------------------------------------------------------------------
        | Health Check
        |----------------------------------------------------------------------
        |
        | Path for the lightweight health check endpoint used by load
        | balancers and deployment platforms. Set to null to disable.
        |
        */
        'health' => '/up',

        /*
        |----------------------------------------------------------------------
        | Reserved Slugs
        |----------------------------------------------------------------------
        |
        | Additional paths that should not be matched by the catch-all page
        | route. The health check path above is automatically included.
        |
        */
        'reserved_slugs' => [],
    ],

];
