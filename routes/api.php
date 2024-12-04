<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Bill\BillController;
use App\Http\Controllers\External\AllBankController;
use App\Http\Controllers\External\BpiController;
use App\Http\Controllers\External\ECCashController;
use App\Http\Controllers\External\ECLoadController;
use App\Http\Controllers\External\ECPayController;
use App\Http\Controllers\External\LalamoveController;
use App\Http\Controllers\External\UnionBankController;
use App\Http\Controllers\External\UPayController;
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Invoice\InvoiceRecordController;
use App\Http\Controllers\Media\MediaController;
use App\Http\Controllers\Merchant\MerchantOnboardController;
use App\Http\Controllers\Merchant\MerchantController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Partners\PartnersController;
use App\Http\Controllers\Product\OrderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Service\PreviousWorkController;
use App\Http\Controllers\Service\ServiceBookingController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Service\ServiceInquiryController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\User\SecurityController;
use App\Http\Controllers\User\SignUpController;
use App\Http\Controllers\Wallet\BalanceController;
use App\Http\Controllers\Invoice\InvoicePayController;
use App\Http\Controllers\Transaction\E2PTransferController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Wallet\LimitController;
use Illuminate\Support\Facades\Route;

/// Auth Group
Route::group(['prefix' => 'auth'], function () {
    Route::middleware(['checkAppAccess'])->group(function () {
        Route::post('/sign-in', [AuthController::class, 'sign_in']);
        Route::post('/otp-verify', [AuthController::class, 'verify_otp']);
        Route::post('/sign-out', [AuthController::class, 'sign_out'])->middleware(['auth:api', 'scope:auth-pin']);
    });

    Route::middleware(['checkSession'])->group(function () {
        Route::post('/pin', [AuthController::class, 'verify_pin']);
        Route::post('/bio', [AuthController::class, 'generate_token']);
        Route::post('/fcm', [AuthController::class, 'register_push_token']);
    });

});

/// Utils Group
Route::group(['prefix' => 'system', 'middleware' => ['checkAppAccess']], function () {
    Route::get('/settings', [SystemController::class, 'settings']);
});

Route::group(['middleware' => ['checkAppAccess']], function () {
    Route::post('/reset-password-otp', [UserController::class, 'reset_password_otp']);
    Route::post('/reset-password', [UserController::class, 'reset_password']);
});

/// Sign up
Route::group(['prefix' => 'sign-up', 'middleware' => ['checkAppAccess']], function () {
    Route::post('/phone-taken', [SignUpController::class, 'phone_taken']);

    Route::get('/otp', [SignUpController::class, 'generate_otp']);
    Route::post('/otp', [SignUpController::class, 'verify_otp']);

    Route::post('/email-taken', [SignUpController::class, 'email_taken']);
    Route::get('/otp-mail', [SignUpController::class, 'generate_mail_otp']);

    Route::get('/uname-taken', [SignUpController::class, 'username_taken']);

    Route::post('/submit', [SignUpController::class, 'submit']);
});

/// KYC
Route::group(['prefix' => 'kyc', 'middleware' => ['auth:api', 'scope:repay-app']], function () {
    Route::get('/settings', [SignUpController::class, 'settings']);

    Route::middleware(['hasKYC'])->group(function () {
        Route::post('/image', [SignUpController::class, 'upload_image']);
        Route::post('/frames', [SignUpController::class, 'upload_frames']);

        Route::group(['prefix' => 'sanity'], function () {
            Route::post('/selfie', [SignUpController::class, 'selfie_sanity']);
            Route::post('/card', [SignUpController::class, 'card_sanity']);
        });

        Route::post('/liveness', [SignUpController::class, 'verify_liveness']);
        Route::post('/tampering', [SignUpController::class, 'detect_tampering']);
        Route::post('/compare', [SignUpController::class, 'compare_faces']);
        Route::post('/complete', [SignUpController::class, 'complete_kyc']);
    });

    Route::get('/image', [SignUpController::class, 'image']);
});

Route::middleware(['auth:api', 'scope:repay-app'])->group(function () {
    /// User Group
    Route::group(['prefix' => 'user'], function () {
        /// Gets
        Route::get('/details', [UserController::class, 'details']);
        Route::get('/preview', [UserController::class, 'preview']);
        Route::post('/email-otp', [UserController::class, 'generate_email_otp']);
        Route::post('/verify-email', [UserController::class, 'verify_email']);
        Route::post('/rh-unlink', [UserController::class, 'rh_unlink']);

        Route::group(['prefix' => 'update'], function () {
            Route::get('/settings', [ProfileController::class, 'settings']);

            Route::middleware(['hasProfileUpdateRequest'])->group(function () {
                Route::post('/image', [ProfileController::class, 'upload_image']);
                Route::post('/frames', [ProfileController::class, 'upload_frames']);

                Route::group(['prefix' => 'sanity'], function () {
                    Route::post('/selfie', [ProfileController::class, 'selfie_sanity']);
                    Route::post('/card', [ProfileController::class, 'card_sanity']);
                });

                Route::post('/liveness', [ProfileController::class, 'verify_liveness']);
                Route::post('/tampering', [ProfileController::class, 'detect_tampering']);
                Route::post('/compare', [ProfileController::class, 'compare_faces']);
                Route::post('/complete', [ProfileController::class, 'update']);
            });
        });
    });

    /// Security Group
    Route::group(['prefix' => 'security'], function () {
        Route::post('/generate-otp', [SecurityController::class, 'generate_otp']);
        Route::post('/change-password', [SecurityController::class, 'change_password']);
        Route::post('/change-pin', [SecurityController::class, 'change_pin']);
    });

    /// Product Group
    Route::group(['prefix' => 'product'], function () {
        /// Gets
        Route::get('/list', [ProductController::class, 'list']);
        Route::get('/categories', [ProductController::class, 'categories']);
        Route::get('/details', [ProductController::class, 'details']);
        Route::get('/active_categories', [ProductController::class, 'active_categories']);
        Route::get('/owned', [ProductController::class, 'owned']);

        Route::get('/location', [ProductController::class, 'by_locations']);

        /// Posts
        Route::post('/enlist', [ProductController::class, 'enlist']);
        Route::post('/update', [ProductController::class, 'update']);


        Route::group(['prefix' => 'order'], function () {
            Route::get('/shipping_options', [OrderController::class, 'shipping_options']);
            Route::get('/details', [OrderController::class, 'details']);

            Route::get('/inbound', [OrderController::class, 'inbound']);
            Route::get('/outbound', [OrderController::class, 'outbound']);

            Route::post('/place', [OrderController::class, 'place_order']);
        });
    });

    /// Service Group
    Route::group(['prefix' => 'service'], function () {
        /// Gets
        Route::get('/list', [ServiceController::class, 'list']);
        Route::get('/categories', [ServiceController::class, 'categories']);
        Route::get('/details', [ServiceController::class, 'details']);
        Route::get('/active_categories', [ServiceController::class, 'active_categories']);
        Route::get('/owned', [ServiceController::class, 'owned']);

        /// Posts
        Route::post('/enlist', [ServiceController::class, 'enlist']);
        Route::group(['prefix' => 'update'], function () {
            Route::post('/info', [ServiceController::class, 'update_info']);
            Route::post('/inquiry', [ServiceController::class, 'update_inquiry_form']);
        });

        Route::group(['prefix' => 'previous'], function () {
            Route::get('/list', [PreviousWorkController::class, 'get_previous_works']);
            Route::get('/get', [PreviousWorkController::class, 'previous_work_details']);

            Route::post('/add', [PreviousWorkController::class, 'add_previous_work']);
            Route::post('/update', [PreviousWorkController::class, 'update_previous_work']);
            Route::post('/delete', [PreviousWorkController::class, 'delete_previous_work']);
        });

        Route::group(['prefix' => 'inquire'], function () {
            Route::get('/merchant-list', [ServiceInquiryController::class, 'merchant_list']);
            Route::get('/client-list', [ServiceInquiryController::class, 'client_list']);

            Route::post('/delete', [ServiceInquiryController::class, 'delete']);
            Route::post('/submit', [ServiceInquiryController::class, 'inquire']);
            Route::post('/quotation', [ServiceInquiryController::class, 'send_quotation']);
        });

        Route::group(['prefix' => 'booking'], function () {
            Route::get('/details', [ServiceBookingController::class, 'details']);

            Route::get('/dates-by-month', [ServiceBookingController::class, 'dates_by_month']);
            Route::get('/list-by-date', [ServiceBookingController::class, 'list_by_date']);
            Route::get('/fulfilled', [ServiceBookingController::class, 'fulfilled']);

            Route::post('/submit', [ServiceBookingController::class, 'book']);
            Route::post('/from-inquiry', [ServiceBookingController::class, 'book_from_inquiry']);

            Route::post('/fulfill', [ServiceBookingController::class, 'fulfill_service']);
            Route::post('/respond', [ServiceBookingController::class, 'booking_respond']);
            Route::post('/cancel', [ServiceBookingController::class, 'cancel']);
        });
    });

    /// Invoice
    Route::group(['prefix' => 'invoice'], function () {
        /// Gets
        Route::get('/details', [InvoiceController::class, 'details']);
        Route::get('/preview', [InvoiceController::class, 'preview']);
        Route::get('/list', [InvoiceController::class, 'list']);

        /// Posts
        Route::post('/e2p', [InvoiceController::class, 'E2PIssue']);
        Route::post('/e2m', [InvoiceController::class, 'E2MIssue']);
        Route::post('/pay', InvoicePayController::class);
        Route::post('/record', InvoiceRecordController::class);
    });


    /// Bills
    Route::group(['prefix' => 'bill'], function () {
        /// Gets
        Route::get('/list', [BillController::class, 'list']);
        Route::get('/details', [BillController::class, 'details']);
        Route::get('/share', [BillController::class, 'share_list']);

        /// Posts
        Route::post('/delete', [BillController::class, 'delete']);
        Route::post('/share', [BillController::class, 'share']);
        Route::post('/unshare', [BillController::class, 'unshare']);
    });


    /// Merchant Group
    Route::group(['prefix' => 'merchant'], function () {
        /// Gets
        Route::get('/categories', [MerchantController::class, 'categories']);
        Route::get('/list', [MerchantController::class, 'list']);
        Route::get('/details', [MerchantController::class, 'details']);
        Route::get('/warehouses', [MerchantController::class, 'warehouses']);

        /// Posts
        Route::post('/onboard', MerchantOnboardController::class);

    });

    /// Transaction Group
    Route::group(['prefix' => 'txn'], function () {
        /// Gets
        Route::get('/list', [TransactionController::class, 'list']);
        Route::get('/details', [TransactionController::class, 'details']);

        /// Posts
        Route::post('/transfer', E2PTransferController::class);
        Route::post('/qr-transfer', [TransactionController::class, 'qr_transfer']);
        Route::post('/qr', [TransactionController::class, 'generate_qr']);
        Route::get('/qr', [TransactionController::class, 'validate_qr']);

        Route::get('/otp', [TransactionController::class, 'generate_otp']);

        Route::get('/reports', [TransactionController::class, 'reports']);
    });

    /// Wallet Group
    Route::group(['prefix' => 'wallet'], function () {
        /// Gets
        Route::get('/balance', BalanceController::class);
        Route::get('/limits', LimitController::class);

        /// Posts
    });

    /// Notification
    Route::group(['prefix' => 'notification'], function () {
        /// Gets
        Route::get('/list', [NotificationController::class, 'list']);
        Route::get('/count', [NotificationController::class, 'count']);
        Route::get('/read', [NotificationController::class, 'set_to_read']);

        /// Posts
        Route::post('/affiliation', [NotificationController::class, 'affiliation']);
    });

    /// Review
    Route::group(['prefix' => 'review'], function () {
        /// Gets
        Route::get('/product', [ReviewController::class, 'list_product']);
        Route::get('/service', [ReviewController::class, 'list_service']);
        Route::get('/merchant', [ReviewController::class, 'list_merchant']);

        /// Posts
        Route::post('/product', [ReviewController::class, 'product_review']);
        Route::post('/service', [ReviewController::class, 'service_review']);
        Route::post('/merchant', [ReviewController::class, 'merchant_review']);
    });

    Route::group(['prefix' => 'media'], function () {

        /// Posts
        Route::group(['prefix' => 'product'], function () {
            Route::post('/update', [MediaController::class, 'update_product_media']);
        });

        Route::group(['prefix' => 'service'], function () {
            Route::post('/update', [MediaController::class, 'update_service_media']);
        });
    });
});


/* --------------------------------------------------------------
 * External
 * - 3rd party integrations
 *
 */
Route::prefix('external')->group(function () {
    /// UPAY
    Route::middleware(['upayAutopost'])->prefix('upay')->group(function () {
        Route::post('webhook', [UPayController::class, 'auto_post']);
    });

    /// UnionBank
    Route::prefix('ub')->group(function () {
        /// Client Endpoints
        Route::middleware(['auth:api', 'scope:repay-app'])->group(function () {
            /// Linking
            Route::get('link', [UnionBankController::class, 'link_url']);
            Route::post('link', [UnionBankController::class, 'link_token']);

            /// Bills
            Route::prefix('bill')->group(function () {
                Route::get('billers', [UnionBankController::class, 'billers']);
                Route::get('preferences', [UnionBankController::class, 'biller_preferences']);

                Route::post('pay', [UnionBankController::class, 'bill_payment']);
            });

            Route::group(['prefix' => 'cash-in', 'as' => 'cash-in.'], function () {
                Route::post('otp', [UnionBankController::class, 'topup_otp']);
                Route::post('pay', [UnionBankController::class, 'topup_payment']);
            });
            Route::post('cash-out', [UnionBankController::class, 'partner_transfer']);

            // Route::get('test', [UnionBankController::class, 'test']);
        });
    });

    /// ECPay
    Route::prefix('ecpay')->group(function () {
        /// Webhook Endpoint
        Route::middleware(['ecpayWebhook'])->post('webhook', [ECPayController::class, 'webhook']);

        /// Client Endpoint

        Route::middleware(['auth:api', 'scope:repay-app'])->group(function () {
            Route::prefix('bill')->group(function () {
                Route::get('billers', [ECPayController::class, 'billers']);
                Route::post('validate', [ECPayController::class, 'bill_validate']);

                Route::post('pay', [ECPayController::class, 'bill_pay']);
                Route::post('create', [ECPayController::class, 'create']);
            });

            Route::prefix('telco')->group(function () {
                Route::get('balance', [ECLoadController::class, 'balance']);
                Route::get('providers', [ECLoadController::class, 'telcos']);
                Route::get('variants', [ECLoadController::class, 'variants']);
            });

            Route::prefix('cash')->group(function () {
                Route::get('services', [ECCashController::class, 'services']);
            });
        });
    });


    /// AllBank
    Route::prefix('alb')->group(function () {
        /// Webhook Endpoints
        Route::post('p2m', [AllBankController::class, 'p2m']);
        Route::post('instapay', [AllBankController::class, 'instapay']);
        Route::post('pesonet', [AllBankController::class, 'pesonet']);
        Route::post('intra', [AllBankController::class, 'intra']);

        /// Client Endpoints
        Route::middleware(['auth:api', 'scope:repay-app'])->group(function () {
            Route::post('dto', [AllBankController::class, 'dto']);

            Route::prefix('qr')->group(function () {
                Route::post('generate', [AllBankController::class, 'generate_qr']);
                Route::post('check', [AllBankController::class, 'P2MCheck']);
                Route::post('cancel', [AllBankController::class, 'P2MCancel']);
            });

            Route::prefix('ipay')->group(function () {

                Route::post('banks', [AllBankController::class, 'insta_banks']);
                Route::post('transfer', [AllBankController::class, 'insta_transfer']);
                Route::post('status', [AllBankController::class, 'insta_status']);
            });

            Route::prefix('pnet')->group(function () {
                Route::post('banks', [AllBankController::class, 'pesonet_banks']);
                Route::post('transfer', [AllBankController::class, 'pesonet_transfer']);
                Route::post('status', [AllBankController::class, 'pesonet_status']);
            });

            Route::prefix('intra')->group(function () {
                Route::post('transfer', [AllBankController::class, 'intra_transfer']);
                Route::post('status', [AllBankController::class, 'intra_status']);
            });
        });


        /// Account Inquiry
        // Route::prefix('account')->group(function () {
        //     Route::post('inq', [AllBankController::class, 'inq']);
        //     Route::post('soa', [AllBankController::class, 'soa']);
        // });
    });

    /* -----------
     * BPI
     */
    Route::group(['prefix' => 'bpi'], function () {
        Route::get('/login', [BpiController::class, 'login']);
    });


    /* -----------
     * LALAMOVE
     */
    Route::group(['prefix' => 'lalamove'], function () {
        Route::post('webhook', [LalamoveController::class, 'webhook']);

        Route::middleware(['auth:api', 'scope:repay-app'])->group(function () {
            // Route::get('cities', [LalamoveController::class, 'city_info']);
            Route::post('quotation', [LalamoveController::class, 'quotation']);
            Route::post('place-order', [LalamoveController::class, 'place_order']);
        });
    });
});


Route::prefix('partners')->middleware(['partners:qr-generate'])->group(function () {
    Route::post('/generate_qr', [PartnersController::class, 'generate_qr']);
});