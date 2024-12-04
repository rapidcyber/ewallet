<?php


// Admin Routes
use App\Admin\Dashboard\AdminDashboard;
use App\Admin\Dashboard\SystemBalances;
use App\Admin\Disputes\ReturnOrders\AdminDisputesReturnOrders;
use App\Admin\Disputes\ReturnOrders\AdminDisputesReturnOrdersShow;
use App\Admin\Disputes\Transactions\AdminDisputesTransactions;
use App\Admin\Disputes\Transactions\AdminDisputesTransactionsShow;
use App\Admin\Employees\AdminEmployees;
use App\Admin\Employees\AdminEmployeesCreate;
use App\Admin\Employees\AdminEmployeesShow;
use App\Admin\Inquiries\AdminInquiries;
use App\Admin\Inquiries\AdminInquiriesDetails;
use App\Admin\ManageMerchants\AdminManageMerchantsList;
use App\Admin\ManageMerchants\Show\BasicDetails\AdminManageMerchantsShowBasicDetails;
use App\Admin\ManageMerchants\Show\Disputes\ReturnOrders\AdminManageMerchantsShowDisputesReturnOrders;
use App\Admin\ManageMerchants\Show\Disputes\ReturnOrders\AdminManageMerchantsShowDisputesReturnOrdersDetails;
use App\Admin\ManageMerchants\Show\Disputes\Transactions\AdminManageMerchantsShowDisputesTransactions;
use App\Admin\ManageMerchants\Show\Disputes\Transactions\AdminManageMerchantsShowDisputesTransactionsDetails;
use App\Admin\ManageMerchants\Show\Products\AdminManageMerchantsShowProducts;
use App\Admin\ManageMerchants\Show\Products\AdminManageMerchantsShowProductsDetails;
use App\Admin\ManageMerchants\Show\Services\AdminManageMerchantsShowServices;
use App\Admin\ManageMerchants\Show\Services\AdminManageMerchantsShowServicesDetails;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsCashInflow;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsCashOutflow;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsEmployees;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsEmployeesDetails;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsInvoices;
use App\Admin\ManageMerchants\Show\Transactions\AdminManageMerchantsShowTransactionsPayroll;
use App\Admin\ManageProducts\AdminManageProducts;
use App\Admin\ManageProducts\AdminManageProductsShow;
use App\Admin\ManageServices\AdminManageServices;
use App\Admin\ManageServices\AdminManageServicesShow;
use App\Admin\ManageUsers\AdminManageUsers;
use App\Admin\ManageUsers\AdminManageUsersRequests;
use App\Admin\ManageUsers\AdminManageUsersRequestsShow;
use App\Admin\ManageUsers\Show\AdminManageUsersShowBasicDetails;
use App\Admin\ManageUsers\Show\Disputes\ReturnOrders\AdminManageUsersShowDisputesReturnOrders;
use App\Admin\ManageUsers\Show\Disputes\ReturnOrders\AdminManageUsersShowDisputesReturnOrdersDetails;
use App\Admin\ManageUsers\Show\Disputes\Transactions\AdminManageUsersShowDisputesTransactions;
use App\Admin\ManageUsers\Show\Disputes\Transactions\AdminManageUsersShowDisputesTransactionsDetails;
use App\Admin\ManageUsers\Show\Transactions\AdminManageUsersShowTransactionsCashInflow;
use App\Admin\ManageUsers\Show\Transactions\AdminManageUsersShowTransactionsCashOutflow;
use App\Admin\Payroll\AdminPayroll;
use App\Admin\Payroll\AdminPayrollSend;
use App\Admin\Payroll\AdminPayrollSendBulk;
use App\Admin\Transactions\CashInflow\AdminCashInflow;
use App\Admin\Transactions\CashOutflow\AdminCashOutflow;
use App\Admin\Transactions\CashOutflow\AdminTransactionsCashOutflowCreate;
use App\Admin\Transactions\Invoices\AdminInvoices;
use App\Admin\Transactions\Invoices\AdminInvoicesCreate;
use App\External\Bpi\BpiLogin;
use App\External\Bpi\BpiRedirect;
// Guest Routes
use App\Guest\AboutUs\AboutUs;
use App\Guest\Auth\ForgotPassword;
use App\Guest\Auth\ResetPassword;
use App\Guest\Auth\SignIn;
use App\Guest\ContactUs\ContactUs;
use App\Guest\Data\Deletion;
use App\Guest\Features\FeatureAssets;
use App\Guest\Features\FeatureExplore;
use App\Guest\Features\FeaturePayments;
use App\Guest\Features\FeatureRemit;
use App\Guest\Features\FeatureYolo;
use App\Guest\Home\Home;
use App\Http\Controllers\External\BpiController;
use App\Http\Controllers\SignOutController;
// Merchant Routes
use App\Merchant\FinancialTransaction\Bills\MerchantFinancialTransactionBills;
use App\Merchant\FinancialTransaction\Bills\MerchantFinancialTransactionBillsApprove;
use App\Merchant\FinancialTransaction\CashInflow\MerchantFinancialTransactionCashInflow;
use App\Merchant\FinancialTransaction\CashOutflow\MerchantFinancialTransactionCashOutflow;
use App\Merchant\FinancialTransaction\CashOutflow\MerchantFinancialTransactionCashOutflowApprove;
use App\Merchant\FinancialTransaction\CashOutflow\MerchantFinancialTransactionCashOutflowCreate;
use App\Merchant\FinancialTransaction\Dashboard\MerchantFinancialTransactionDashboard;
use App\Merchant\FinancialTransaction\Employees\EmployeesCreate;
use App\Merchant\FinancialTransaction\Employees\EmployeesShow;
use App\Merchant\FinancialTransaction\Employees\MerchantFinancialTransactionEmployees;
use App\Merchant\FinancialTransaction\Invoices\MerchantInvoices;
use App\Merchant\FinancialTransaction\Invoices\MerchantInvoicesCreate;
use App\Merchant\FinancialTransaction\Payroll\MerchantFinancialTransactionPayroll;
use App\Merchant\FinancialTransaction\Payroll\MerchantFinancialTransactionPayrollBulkUpload;
use App\Merchant\FinancialTransaction\Payroll\MerchantFinancialTransactionPayrollCreate;
use App\Merchant\SellerCenter\Assets\MerchantSellerCenterAssets;
use App\Merchant\SellerCenter\Assets\MerchantSellerCenterAssetsCreate;
use App\Merchant\SellerCenter\Assets\MerchantSellerCenterAssetsEdit;
use App\Merchant\SellerCenter\Assets\MerchantSellerCenterAssetsShow;
use App\Merchant\SellerCenter\Dashboard\MerchantSellerCenterDashboard;
use App\Merchant\SellerCenter\Disputes\MerchantSellerCenterDisputes;
use App\Merchant\SellerCenter\Disputes\MerchantSellerCenterDisputesCreate;
use App\Merchant\SellerCenter\Disputes\MerchantSellerCenterDisputesShow;
use App\Merchant\SellerCenter\Logistics\Orders\MerchantSellerCenterLogisticsOrders;
use App\Merchant\SellerCenter\Logistics\Orders\MerchantSellerCenterLogisticsOrdersShow;
use App\Merchant\SellerCenter\Logistics\ReturnOrders\MerchantSellerCenterLogisticsReturnOrders;
use App\Merchant\SellerCenter\Logistics\ReturnOrders\MerchantSellerCenterLogisticsReturnOrdersShow;
use App\Merchant\SellerCenter\Logistics\WarehouseShipping\MerchantSellerCenterLogisticsWarehouseShipping;
use App\Merchant\SellerCenter\Services\Bookings\MerchantSellerCenterServicesBookings;
use App\Merchant\SellerCenter\Services\Bookings\MerchantSellerCenterServicesBookingsDetails;
use App\Merchant\SellerCenter\Services\Bookings\MerchantSellerCenterServicesBookingsQuotation;
use App\Merchant\SellerCenter\Services\Bookings\MerchantSellerCenterServicesBookingsQuotationCreate;
use App\Merchant\SellerCenter\Services\MerchantSellerCenterServices;
use App\Merchant\SellerCenter\Services\MerchantSellerCenterServicesCreate;
use App\Merchant\SellerCenter\Services\MerchantSellerCenterServicesEdit;
use App\Merchant\SellerCenter\Services\MerchantSellerCenterServicesShow;
use App\Merchant\SellerCenter\StoreManagement\MerchantSellerCenterStoreManagement;
use App\PrivacyPolicy;
// System Admin
use App\SystemAdmin\SystemAdminAppAccess;
use App\SystemAdmin\SystemAdminClients;
use App\SystemAdmin\SystemAdminDashboard;
use App\TermsConditions;
// User Routes
use App\User\Bills\UserBills;
use App\User\CashInflow\UserCashInflow;
use App\User\CashOutflow\UserCashOutflow;
use App\User\CashOutflow\UserCashOutflowCreate;
use App\User\Dashboard\UserDashboard;
use App\User\Disputes\UserDisputes;
use App\User\Disputes\UserDisputesCreate;
use App\User\Disputes\UserDisputesShow;
use App\User\Link\LinkStatus;
use App\User\Link\LinkSuccess;
use App\User\Link\Realholmes;
use App\User\Orders\UserOrders;
use App\User\Orders\UserOrdersShow;
use App\User\ReturnOrders\UserReturnOrders;
use App\User\ReturnOrders\UserReturnOrdersShow;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
// Web Routes
use Illuminate\Support\Facades\Route;

/* ------------------
* Guest Group Routes
*/
Route::group(['middleware' => ['add-csp-headers']], function () {
    Route::get('/login', function () {
        /// Redirect for passport unauthenticated users.
        return redirect()->route('sign-in');
    })->name('login');
    
    Route::get('/contact-us', ContactUs::class)->name('contact-us');
    
    Route::get('/privacy-policy', PrivacyPolicy::class)->name('privacy-policy');
    Route::get('/terms-and-conditions', TermsConditions::class)->name('terms-and-conditions');

    Route::get('/home', function () {
        return redirect()->route('home');
    });

    Route::get('/', Home::class)->name('home');
    Route::get('/about-us', AboutUs::class)->name('about-us');
    Route::get('/data-deletion', Deletion::class)->name('data-deletion');

    /* ----------
     * Auth Group
     */
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/sign-in', SignIn::class)->name('sign-in');
        Route::get('/forgot-password', ForgotPassword::class)->name('forgot-password');
        Route::get('/reset-password', ResetPassword::class)->name('reset-password');
    });

    Route::group(['as' => 'features.', 'prefix' => 'features'], function () {
        Route::get('/remit', FeatureRemit::class)->name('remit');
        Route::get('/explore', FeatureExplore::class)->name('explore');
        Route::get('/payments', FeaturePayments::class)->name('payments');
        Route::get('/assets', FeatureAssets::class)->name('assets');
        Route::get('/yolo', FeatureYolo::class)->name('yolo');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', function () {
        return redirect()->route('sign-in');
    });
    Route::post('/logout', SignOutController::class)->name('logout');

    /* ------------------
     * User Group Routes
     */
    Route::group(['as' => 'user.', 'prefix' => 'user'], function () {
        Route::get('/', function () {
            return redirect()->route('user.dashboard');
        })->name('index');

        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
        Route::get('/cash-inflow', UserCashInflow::class)->name('cash-inflow');
        Route::group(['as' => 'cash-outflow.', 'prefix' => 'cash-outflow'], function () {
            Route::get('/', UserCashOutflow::class)->name('index');
            Route::get('/create', UserCashOutflowCreate::class)->name('create');
        });
        Route::get('/bills', UserBills::class)->name('bills');
        Route::group(['as' => 'orders.', 'prefix' => 'orders'], function () {
            Route::get('/', UserOrders::class)->name('index');
            Route::get('/{productOrder:order_number}', UserOrdersShow::class)->name('show')->scopeBindings();
        });
        Route::group(['as' => 'return-orders.', 'prefix' => 'return-orders'], function () {
            Route::get('/', UserReturnOrders::class)->name('index');
            Route::get('/{returnOrder}', UserReturnOrdersShow::class)->name('show');
        });
        Route::group(['as' => 'disputes.', 'prefix' => 'disputes'], function () {
            Route::get('/', UserDisputes::class)->name('index');
            Route::get('/create', UserDisputesCreate::class)->name('create');
            Route::get('/{transactionDispute}', UserDisputesShow::class)->name('show');
        });
    });

    /* ------------------
     * Merchant Group Routes
     */
    Route::group(['as' => 'merchant.', 'prefix' => 'merchant/{merchant:account_number}', 'middleware' => ['merchant']], function () {
        Route::group(['as' => 'seller-center.', 'prefix' => 'seller-center'], function () {
            Route::get('/', function (Request $request) {
                return redirect()->route('merchant.seller-center.dashboard', ['merchant', $request->route('merchant')]);
            })->name('index');

            Route::get('/dashboard', MerchantSellerCenterDashboard::class)->middleware('can:merchant-sc-dashboard,merchant,"view"')->name('dashboard');
            Route::get('/store-management', MerchantSellerCenterStoreManagement::class)->middleware('can:merchant-store-management,merchant,"view"')->name('store-management');
            Route::group(['as' => 'assets.', 'prefix' => 'assets', 'middleware' => ['can:merchant-products,merchant,"view"']], function () {
                Route::get('/', MerchantSellerCenterAssets::class)->name('index');
                Route::get('/create', MerchantSellerCenterAssetsCreate::class)->middleware('can:merchant-products,merchant,"create"')->name('create');
                Route::get('/{product}', MerchantSellerCenterAssetsShow::class)->name('show');
                Route::get('/{product}/edit', MerchantSellerCenterAssetsEdit::class)->middleware('can:merchant-products,merchant,"update"')->name('edit');
            })->scopeBindings();
            Route::group(['as' => 'services.', 'prefix' => 'services', 'middleware' => ['can:merchant-services,merchant,"view"']], function () {
                Route::get('/', MerchantSellerCenterServices::class)->name('index');
                Route::get('/create', MerchantSellerCenterServicesCreate::class)->middleware('can:merchant-services,merchant,"create"')->name('create');
                Route::get('/{service}', MerchantSellerCenterServicesShow::class)->name('show')->scopeBindings();
                Route::group(['as' => 'show.', 'prefix' => '{service}'], function () {
                    Route::get('/edit', MerchantSellerCenterServicesEdit::class)->middleware('can:merchant-services,merchant,"update"')->name('edit');
                    Route::get('/bookings', MerchantSellerCenterServicesBookings::class)->name('bookings');
                    Route::group(['as' => 'bookings.', 'prefix' => '{type}/{booking}', 'middleware' => ['bookingtype']], function () {
                        Route::get('/', MerchantSellerCenterServicesBookingsDetails::class)->name('details')->where('type', 'inquiries|bookings')->scopeBindings();
                        Route::group(['as' => 'quotation.', 'prefix' => 'quotation'], function () {
                            Route::get('/', MerchantSellerCenterServicesBookingsQuotation::class)->name('index');
                            Route::get('/create', MerchantSellerCenterServicesBookingsQuotationCreate::class)->middleware('can:merchant-invoices,merchant,"create"')->name('create');
                        });
                    });
                });
            })->scopeBindings();
            Route::group(['as' => 'logistics.', 'prefix' => 'logistics'], function () {
                Route::group(['as' => 'orders.', 'prefix' => 'orders', 'middleware' => ['can:merchant-orders,merchant,"view"']], function () {
                    Route::get('/', MerchantSellerCenterLogisticsOrders::class)->name('index');
                    Route::get('/{productOrder:order_number}', MerchantSellerCenterLogisticsOrdersShow::class)->name('show')->withoutScopedBindings();
                });
                Route::group(['as' => 'return-orders.', 'prefix' => 'return-orders', 'middleware' => ['can:merchant-return-orders,merchant,"view"']], function () {
                    Route::get('/', MerchantSellerCenterLogisticsReturnOrders::class)->name('index');
                    Route::get('/{returnOrder}', MerchantSellerCenterLogisticsReturnOrdersShow::class)->name('show')->withoutScopedBindings();
                });
                Route::get('/warehouse-shipping', MerchantSellerCenterLogisticsWarehouseShipping::class)
                    ->middleware('can:merchant-warehouse,merchant,"view"')
                    ->name('warehouse-shipping');
            });
            Route::group(['as' => 'disputes.', 'prefix' => 'disputes', 'middleware' => ['can:merchant-disputes,merchant,"view"']], function () {
                Route::get('/', MerchantSellerCenterDisputes::class)->name('index');
                Route::get('/create', MerchantSellerCenterDisputesCreate::class)->middleware('can:merchant-disputes,merchant,"create"')->name('create');
                Route::get('/{transactionDispute}', MerchantSellerCenterDisputesShow::class)->name('show');
            });
        });

        Route::group(['as' => 'financial-transactions.', 'prefix' => 'financial-transactions'], function () {
            Route::get('/', function (Request $request) {
                return redirect()->route('merchant.financial-transactions.dashboard', ['merchant', $request->route('merchant')]);
            })->name('index');

            Route::get('/dashboard', MerchantFinancialTransactionDashboard::class)->middleware('can:merchant-ft-dashboard,merchant,"view"')->name('dashboard');
            Route::get('/cash-inflow', MerchantFinancialTransactionCashInflow::class)->middleware('can:merchant-cash-inflow,merchant,"view"')->name('cash-inflow');
            Route::group(['as' => 'cash-outflow.', 'prefix' => 'cash-outflow', 'middleware' => ['can:merchant-cash-outflow,merchant,"view"']], function () {
                Route::get('/', MerchantFinancialTransactionCashOutflow::class)->name('index');
                Route::get('/approve', MerchantFinancialTransactionCashOutflowApprove::class)
                    ->middleware(['can:merchant-cash-outflow,merchant,"approve"'])
                    ->name('approve');
                Route::get('/create', MerchantFinancialTransactionCashOutflowCreate::class)
                    ->middleware(['can:merchant-cash-outflow,merchant,"create"'])
                    ->name('create');
            });

            Route::group(['as' => 'bills.', 'prefix' => 'bills', 'middleware' => ['can:merchant-bills,merchant,"view"']], function () {
                Route::get('/', MerchantFinancialTransactionBills::class)->name('index');
                Route::get('/approve', MerchantFinancialTransactionBillsApprove::class)
                    ->middleware(['can:merchant-bills,merchant,"approve"'])
                    ->name('approve'); 
            });

            Route::group(['as' => 'invoices.', 'prefix' => 'invoices', 'middleware' => ['can:merchant-invoices,merchant,"view"']], function () {
                Route::get('/', MerchantInvoices::class)->name('index');
                Route::get('/create', MerchantInvoicesCreate::class)
                    ->middleware(['can:merchant-invoices,merchant,"create"'])
                    ->name('create');
            });

            Route::group(['as' => 'employees.', 'prefix' => 'employees', 'middleware' => 'can:merchant-employees,merchant,"view"'], function () {
                Route::get('/', MerchantFinancialTransactionEmployees::class)->name('index');
                Route::get('/create', EmployeesCreate::class)->middleware('can:merchant-employees,merchant,"create"')->name('create');
                Route::get('/{employee}', EmployeesShow::class)->name('show');
            });

            Route::group(['as' => 'payroll.', 'prefix' => 'payroll', 'middleware' => 'can:merchant-payroll,merchant,"view"'], function () {
                Route::get('/', MerchantFinancialTransactionPayroll::class)->name('index');
                Route::get('/create', MerchantFinancialTransactionPayrollCreate::class)->middleware('can:merchant-payroll,merchant,"create"')->name('create');
                Route::get('/bulk-upload', MerchantFinancialTransactionPayrollBulkUpload::class)->middleware('can:merchant-payroll,merchant,"create"')->name('bulk-upload');
            });
        });
    });

    /* ------------------
     * Admin Group Routes
     */
    Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'role:administrator'], function () {
        Route::get('/', function (Request $request) {
            return redirect()->route('admin.dashboard');
        })->name('index');

        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/balances', SystemBalances::class)->name('system-balances');

        Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
            Route::get('/cash-inflow', AdminCashInflow::class)->name('cash-inflow');
            Route::group(['as' => 'cash-outflow.', 'prefix' => 'cash-outflow'], function () {
                Route::get('/', AdminCashOutflow::class)->name('index');
                Route::get('/create', AdminTransactionsCashOutflowCreate::class)->name('create');
            });
            Route::group(['as' => 'invoices.', 'prefix' => 'invoices'], function () {
                Route::get('/', AdminInvoices::class)->name('index');
                Route::get('/create', AdminInvoicesCreate::class)->name('create');
            });
        });

        Route::group(['as' => 'manage-users.', 'prefix' => 'manage-users'], function () {
            Route::get('/', AdminManageUsers::class)->name('index');
            Route::get('/requests', AdminManageUsersRequests::class)->name('requests');
            
            Route::group(['as' => 'requests.', 'prefix' => 'requests'], function () {
                Route::get('/', AdminManageUsersRequests::class)->name('index');
                Route::get('/{profileUpdateRequest}', AdminManageUsersRequestsShow::class)->name('show');
            });

            Route::group(['as' => 'show.', 'prefix' => '{user}'], function () {
                Route::get('/', AdminManageUsersShowBasicDetails::class)->name('basic-details');
                Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
                    Route::get('/cash-inflow', AdminManageUsersShowTransactionsCashInflow::class)->name('cash-inflow');
                    Route::get('/cash-outflow', AdminManageUsersShowTransactionsCashOutflow::class)->name('cash-outflow');
                });
                Route::group(['as' => 'disputes.', 'prefix' => 'disputes'], function () {
                    Route::group(['as' => 'return-orders.', 'prefix' => 'return-orders'], function () {
                        Route::get('/', AdminManageUsersShowDisputesReturnOrders::class)->name('index');
                        Route::get('/{returnOrder}', AdminManageUsersShowDisputesReturnOrdersDetails::class)->name('details');
                    });
                    Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
                        Route::get('/', AdminManageUsersShowDisputesTransactions::class)->name('index');
                        Route::get('/{transactionDispute}', AdminManageUsersShowDisputesTransactionsDetails::class)->name('details');
                    });
                });
            });
        });

        Route::group(['as' => 'manage-merchants.', 'prefix' => 'manage-merchants'], function () {
            Route::get('/', AdminManageMerchantsList::class)->name('index');
            Route::group(['as' => 'show.', 'prefix' => '{merchant}'], function () {
                Route::get('/', AdminManageMerchantsShowBasicDetails::class)->name('basic-details');
                Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
                    Route::get('/cash-inflow', AdminManageMerchantsShowTransactionsCashInflow::class)->name('cash-inflow');
                    Route::get('/cash-outflow', AdminManageMerchantsShowTransactionsCashOutflow::class)->name('cash-outflow');
                    Route::get('/invoices', AdminManageMerchantsShowTransactionsInvoices::class)->name('invoices');
                    Route::get('/payroll', AdminManageMerchantsShowTransactionsPayroll::class)->name('payroll');

                    Route::group(['as' => 'employees.', 'prefix' => 'employees'], function () {
                        Route::get('/', AdminManageMerchantsShowTransactionsEmployees::class)->name('index');
                        Route::get('/{employee}', AdminManageMerchantsShowTransactionsEmployeesDetails::class)->name('details');
                    });
                });
                Route::group(['as' => 'disputes.', 'prefix' => 'disputes'], function () {
                    Route::group(['as' => 'return-orders.', 'prefix' => 'return-orders'], function () {
                        Route::get('/', AdminManageMerchantsShowDisputesReturnOrders::class)->name('index');
                        Route::get('/{returnOrder}', AdminManageMerchantsShowDisputesReturnOrdersDetails::class)->name('details');
                    });
                    Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
                        Route::get('/', AdminManageMerchantsShowDisputesTransactions::class)->name('index');
                        Route::get('/{transactionDispute}', AdminManageMerchantsShowDisputesTransactionsDetails::class)->name('details');
                    });
                });
                Route::group(['as' => 'products.', 'prefix' => 'products'], function () {
                    Route::get('/', AdminManageMerchantsShowProducts::class)->name('index');
                    Route::get('/{product}', AdminManageMerchantsShowProductsDetails::class)->name('details');
                });
                Route::group(['as' => 'services.', 'prefix' => 'services'], function () {
                    Route::get('/', AdminManageMerchantsShowServices::class)->name('index');
                    Route::get('/{service}', AdminManageMerchantsShowServicesDetails::class)->name('details');
                });
            });
        });

        Route::group(['as' => 'manage-products.', 'prefix' => 'manage-products'], function () {
            Route::get('/', AdminManageProducts::class)->name('index');
            Route::get('/{product}', AdminManageProductsShow::class)->name('show');
        });

        Route::group(['as' => 'manage-services.', 'prefix' => 'manage-services'], function () {
            Route::get('/', AdminManageServices::class)->name('index');
            Route::get('/{service}', AdminManageServicesShow::class)->name('show');
        });

        Route::group(['as' => 'employees.', 'prefix' => 'employees'], function () {
            Route::get('/', AdminEmployees::class)->name('index');
            Route::get('/create', AdminEmployeesCreate::class)->name('create');
            Route::get('/{employee}', AdminEmployeesShow::class)->name('show');
        });

        Route::group(['as' => 'payroll.', 'prefix' => 'payroll'], function () {
            Route::get('/', AdminPayroll::class)->name('index');
            Route::get('/send', AdminPayrollSend::class)->name('send');
            Route::get('/send-bulk', AdminPayrollSendBulk::class)->name('send-bulk');
        });

        Route::group(['as' => 'disputes.', 'prefix' => 'disputes'], function () {
            Route::group(['as' => 'return-orders.', 'prefix' => 'return-orders'], function () {
                Route::get('/', AdminDisputesReturnOrders::class)->name('index');
                Route::get('/{returnOrder}', AdminDisputesReturnOrdersShow::class)->name('show');
            });

            Route::group(['as' => 'transactions.', 'prefix' => 'transactions'], function () {
                Route::get('/', AdminDisputesTransactions::class)->name('index');
                Route::get('/{transactionDispute}', AdminDisputesTransactionsShow::class)->name('show');
            });
        });

        Route::group(['as' => 'inquiries.', 'prefix' => 'inquiries'], function () {
            Route::get('/', AdminInquiries::class)->name('index');
            Route::get('/{inquiry}', AdminInquiriesDetails::class)->name('show');
        });
    });

    /* --------------------
     * System Admin Routes
     */
    Route::group(['as' => 'sys.', 'prefix' => 'sys', 'middleware' => ['isSystemAdmin']], function () {
        Route::get('/client', SystemAdminClients::class)->name('clients');
        Route::get('/app-access', SystemAdminAppAccess::class)->name('app-access');
    });
});

/// Account linking
Route::group(['as' => 'link.', 'prefix' => 'link'], function () {
    Route::get('/status', LinkStatus::class)->name('status');
    Route::get('/realholmes', Realholmes::class)->name('realholmes');
});

// External
Route::group(['as' => 'external.', 'prefix' => 'external'], function () {
    // BPI
    Route::group(['as' => 'bpi.', 'prefix' => 'bpi'], function () {
        Route::get('/callback', [BpiController::class, 'callback'])->name('callback');
    });
});