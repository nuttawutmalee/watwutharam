<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => [\App\Api\Middleware\ApiCmsApplication::class], 'namespace' => 'App\Api\V1\Controllers'], function ($api) {
    $api->post('/user/login', 'UserController@login');
    $api->post('/db/unlock', 'DatabaseController@unlock');

    //Helpers
    $api->group(['prefix' => '/helpers'], function ($api) {
        $api->get('/query/{item_option_id}', [
            'as' => 'helpers.query',
            'uses' => 'HelperController@getControlListQuery'
        ]);

        $api->group(['prefix' => '/site/{domain_name}'], function ($api) {
            $api->get('/', 'HelperController@getSiteData');

            $api->post('/sitemap', 'HelperController@getSiteMap');
            $api->get('/sitemap.xml', 'HelperController@generateMainSiteMapXML');
            $api->get('/{language_code}/sitemap.xml', 'HelperController@generateSiteMapXML');

            $api->post('/redirectUrl', 'HelperController@getRedirectUrlBySourceUrl');

            $api->get('/translations/{language_code?}', 'HelperController@getSiteTranslations');
            $api->get('/translation/{item_option_id}/{language_code?}', 'HelperController@getSiteTranslationByItemOptionId');

            $api->get('/globalItems/{language_code?}', 'HelperController@getGlobalItemData');

            $api->get('/form/{variable_name}/{language_code?}', 'HelperController@getFormPropertyData');
            $api->post('/form/{variable_name}/{language_code?}', 'HelperController@saveFormPropertyData');

            $api->group(['prefix' => '/page'], function ($api) {
                $api->post('/', 'HelperController@getPageData');
                $api->post('/search-by-categories', 'HelperController@getPagesByCategories');
                $api->post('/search-by-template', 'HelperController@getPagesByTemplate');
                $api->post('/parents', 'HelperController@getParentPagesDataByFriendlyUrl');
                $api->post('/children', 'HelperController@getChildPagesDataByFriendlyUrl');
            });
        });
    });
});

$api->version('v1', ['middleware' => [\App\Api\Middleware\ApiCmsApplication::class, 'api'], 'namespace' => 'App\Api\V1\Controllers'] , function ($api) {

    $api->get('/cache-links', 'HelperController@getCacheLinks');

    //Category Name
    $api->get('/categoryNames', 'CategoryNameController@getCategoryNames');

    //Dashboard
    $api->group(['prefix' => '/dashboard'], function ($api) {
        $api->post('/search', 'DashboardController@searchOptions');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'DashboardController@getDashboardData');
        });
    });
    
    //Upload
    $api->get('/uploads', 'UploadController@listUploads');
    $api->post('/quick-upload', 'UploadController@quickUpload');
    $api->post('/multiple-upload', 'UploadController@multipleUpload');

    //User
    $api->group(['prefix' => '/users'], function ($api) {
        $api->get('/', 'UserController@getUsers');
        $api->post('/update', 'UserController@update');
        $api->delete('/', 'UserController@delete');
    });

    $api->group(['prefix' => '/user'], function ($api) {
        $api->post('/logout', 'UserController@logout');
        $api->post('/register', 'UserController@register');
        $api->post('/is-loggedin', 'UserController@checkAuth');
        $api->post('/search-by-email', 'UserController@getUserByEmail');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'UserController@getUserById');
            $api->post('/update', 'UserController@updateById');
            $api->delete('/', 'UserController@deleteById');
        });
    });

    //Cms Role
    $api->group(['prefix' => '/roles'], function ($api) {
        $api->get('/', 'CmsRoleController@getRoles');
    });
    
    //Language
    $api->group(['prefix' => '/languages'], function ($api) {
        $api->get('/', 'LanguageController@getLanguages');
        $api->post('/update', 'LanguageController@update');
        $api->delete('/', 'LanguageController@delete');
    });

    $api->group(['prefix' => '/language'], function ($api) {
        $api->post('/', 'LanguageController@store');

        $api->group(['prefix' => '{code}'], function ($api) {
            $api->get('/', 'LanguageController@getLanguageByCode');
            $api->post('/update', 'LanguageController@updateByCode');
            $api->delete('/', 'LanguageController@deleteByCode');
        });
    });

    //Site
    $api->group(['prefix' => '/sites'], function ($api) {
        $api->get('/', 'SiteController@getSites');
        $api->post('/update', 'SiteController@update');
        $api->delete('/', 'SiteController@delete');
    });

    $api->group(['prefix' => '/site'], function ($api) {
        $api->post('/', 'SiteController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'SiteController@getSiteById');
            $api->post('/update', 'SiteController@updateById');
            $api->delete('/', 'SiteController@deleteById');

            //Relationships
            $api->get('/redirectUrls', 'SiteController@getRedirectUrls');
            $api->get('/globalItems', 'SiteController@getGlobalItems');
            $api->get('/globalItems/{component_id}', 'SiteController@getGlobalItemsByComponentId');
            $api->get('/templates', 'SiteController@getTemplates');
            $api->get('/pages', 'SiteController@getPages');
            $api->post('/page-preview', 'SiteController@getPagePreview');

            $api->get('/siteTranslations', 'SiteController@getSiteTranslations');
            $api->get('/siteTranslations/{code}', 'SiteController@getSiteTranslationsByLanguageCode');

            $api->get('/submissions-name', 'SiteController@getSubmissionNames');
            $api->get('/submissions/{variable_name?}', 'SiteController@getSubmission');
            $api->delete('/submissions/{variable_name?}', 'SiteController@clearSubmission');
            $api->get('/export-submissions/{variable_name}', 'SiteController@exportSubmission');

            $api->get('/export-translations', 'SiteController@exportTranslations');
            $api->post('/import-translations', 'SiteController@importTranslations');

            //Languages
            $api->group(['prefix' => '/languages'], function ($api) {
                $api->get('/', 'SiteController@getSiteLanguagesBySiteId');
                $api->post('/update', 'SiteController@updateSiteLanguagesBySiteId');
                $api->delete('/', 'SiteController@detachSiteLanguagesBySiteId');

                $api->post('/reorder', 'SiteController@reorderSiteLanguagesBySiteId');
            });

            $api->group(['prefix' => '/language'], function ($api) {
                $api->post('/', 'SiteController@attachSiteLanguageBySiteId');

                $api->group(['prefix' => '{code}'], function ($api) {
                    $api->get('/', 'SiteController@getSiteLanguageBySiteIdAndCode');
                    $api->post('/update', 'SiteController@updateSiteLanguageBySiteIdAndCode');
                    $api->delete('/', 'SiteController@detachSiteLanguageBySiteIdAndCode');
                });
            });
        });
    });

    //Redirect Url
    $api->group(['prefix' => '/redirectUrls'], function ($api) {
        $api->get('/', 'RedirectUrlController@getRedirectUrls');
        $api->post('/update', 'RedirectUrlController@update');
        $api->delete('/', 'RedirectUrlController@delete');
    });

    $api->group(['prefix' => '/redirectUrl'], function ($api) {
        $api->post('/', 'RedirectUrlController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'RedirectUrlController@getRedirectUrlByIdOrSourceUrl');
            $api->post('/update', 'RedirectUrlController@updateById');
            $api->delete('/', 'RedirectUrlController@deleteById');
        });
    });

    //Component
    $api->group(['prefix' => '/components'], function ($api) {
        $api->get('/', 'ComponentController@getComponents');
        $api->post('/update', 'ComponentController@update');
        $api->delete('/', 'ComponentController@delete');
    });

    $api->group(['prefix' => '/component'], function ($api) {
        $api->post('/', 'ComponentController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'ComponentController@getComponentById');
            $api->post('/update', 'ComponentController@updateById');
            $api->delete('/', 'ComponentController@deleteById');

            $api->get('/componentOptions', 'ComponentController@getComponentOptionsByComponentId');
//            $api->post('/componentOptions/reorder', 'ComponentController@reorderComponentOptionsByComponentId');

            $api->get('/templateItems', 'ComponentController@getTemplateItems');
            $api->get('/pageItems', 'ComponentController@getPageItems');
            $api->get('/globalItems', 'ComponentController@getGlobalItems');

            $api->group(['prefix' => '/inheritances'], function ($api) {
                $api->get('/', 'ComponentController@getInheritances');
                $api->get('/{site_id}', 'ComponentController@getInheritancesBySiteId');
                $api->post('/update', 'ComponentController@updateInheritances');
            });
        });
    });

    //Component Option
    $api->group(['prefix' => '/componentOptions'], function ($api) {
        $api->get('/', 'ComponentOptionController@getComponentOptions');
        $api->post('/update', 'ComponentOptionController@update');
        $api->delete('/', 'ComponentOptionController@delete');
    });

    $api->group(['prefix' => '/componentOption'], function ($api) {
        $api->post('/', 'ComponentOptionController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'ComponentOptionController@getComponentOptionById');
            $api->post('/update', 'ComponentOptionController@updateById');
            $api->delete('/', 'ComponentOptionController@deleteById');
        });
    });

    //Template
    $api->group(['prefix' => '/templates'], function ($api) {
        $api->get('/', 'TemplateController@getTemplates');
        $api->post('/update', 'TemplateController@update');
        $api->delete('/', 'TemplateController@delete');
    });

    $api->group(['prefix' => '/template'], function ($api) {
        $api->post('/', 'TemplateController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'TemplateController@getTemplateById');
            $api->post('/update', 'TemplateController@updateById');
            $api->delete('/', 'TemplateController@deleteById');

            $api->get('/pages', 'TemplateController@getPagesByTemplateId');
            $api->get('/templateItems', 'TemplateController@getTemplateItemsByTemplateId');
            $api->post('/templateItems/reorder', 'TemplateController@reorderTemplateItemsByTemplateId');
        });
    });

    //Template Item
    $api->group(['prefix' => '/templateItems'], function ($api) {
        $api->get('/', 'TemplateItemController@getTemplateItems');
        $api->post('/update', 'TemplateItemController@update');
        $api->delete('/', 'TemplateItemController@delete');
    });

    $api->group(['prefix' => '/templateItem'], function ($api) {
        $api->post('/', 'TemplateItemController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'TemplateItemController@getTemplateItemById');
            $api->post('/update', 'TemplateItemController@updateById');
            $api->delete('/', 'TemplateItemController@deleteById');

            $api->get('/templateItemOptions', 'TemplateItemController@getTemplateItemOptionsByTemplateItemId');
//            $api->post('/templateItemOptions/reorder', 'TemplateItemController@reorderTemplateItemOptionsByTemplateItemId');
        });
    });

    //Template Item Option
    $api->group(['prefix' => '/templateItemOptions'], function ($api) {
        $api->get('/', 'TemplateItemOptionController@getTemplateItemOptions');
        $api->post('/update', 'TemplateItemOptionController@update');
        $api->delete('/', 'TemplateItemOptionController@delete');
    });

    $api->group(['prefix' => '/templateItemOption'], function ($api) {
        $api->post('/', 'TemplateItemOptionController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'TemplateItemOptionController@getTemplateItemOptionById');
            $api->post('/update', 'TemplateItemOptionController@updateById');
            $api->delete('/', 'TemplateItemOptionController@deleteById');
        });
    });

    //Page
    $api->group(['prefix' => '/pages'], function ($api) {
        $api->get('/', 'PageController@getPages');
        $api->post('/update', 'PageController@update');
        $api->delete('/', 'PageController@delete');
    });

    $api->group(['prefix' => '/page'], function ($api) {
        $api->post('/', 'PageController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'PageController@getPageById');
            $api->post('/update', 'PageController@updateById');
            $api->delete('/', 'PageController@deleteById');

            $api->get('/pageItems', 'PageController@getPageItemsByPageId');
            $api->post('/pageItems/reorder', 'PageController@reorderPageItemsByPageId');
        });
    });

    //Page Item
    $api->group(['prefix' => '/pageItems'], function ($api) {
        $api->get('/', 'PageItemController@getPageItems');
        $api->post('/update', 'PageItemController@update');
        $api->delete('/', 'PageItemController@delete');
    });

    $api->group(['prefix' => '/pageItem'], function ($api) {
        $api->post('/', 'PageItemController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'PageItemController@getPageItemById');
            $api->post('/update', 'PageItemController@updateById');
            $api->delete('/', 'PageItemController@deleteById');

            $api->get('/pageItemOptions', 'PageItemController@getPageItemOptionsByPageItemId');
//            $api->post('/pageItemOptions/reorder', 'PageItemController@reorderPageItemOptionsByPageItemId');
        });
    });

    //Page Item Option
    $api->group(['prefix' => '/pageItemOptions'], function ($api) {
        $api->get('/', 'PageItemOptionController@getPageItemOptions');
        $api->post('/update', 'PageItemOptionController@update');
        $api->delete('/', 'PageItemOptionController@delete');
    });

    $api->group(['prefix' => '/pageItemOption'], function ($api) {
        $api->post('/', 'PageItemOptionController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'PageItemOptionController@getPageItemOptionById');
            $api->post('/update', 'PageItemOptionController@updateById');
            $api->delete('/', 'PageItemOptionController@deleteById');
        });
    });

    //Global Item
    $api->group(['prefix' => '/globalItems'], function ($api) {
        $api->get('/', 'GlobalItemController@getGlobalItems');
        $api->post('/update', 'GlobalItemController@update');
        $api->delete('/', 'GlobalItemController@delete');
    });

    $api->group(['prefix' => '/globalItem'], function ($api) {
        $api->post('/', 'GlobalItemController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'GlobalItemController@getGlobalItemById');
            $api->post('/update', 'GlobalItemController@updateById');
            $api->delete('/', 'GlobalItemController@deleteById');

            $api->get('/globalItemOptions', 'GlobalItemController@getGlobalItemOptionsByGlobalItemId');
//            $api->post('/globalItemOptions/reorder', 'GlobalItemController@reorderGlobalItemOptionsByGlobalItemId');
        });
    });

    //Global Item Option
    $api->group(['prefix' => '/globalItemOptions'], function ($api) {
        $api->get('/', 'GlobalItemOptionController@getGlobalItemOptions');
        $api->post('/update', 'GlobalItemOptionController@update');
        $api->delete('/', 'GlobalItemOptionController@delete');
    });

    $api->group(['prefix' => '/globalItemOption'], function ($api) {
        $api->post('/', 'GlobalItemOptionController@store');

        $api->group(['prefix' => '{id}'], function ($api) {
            $api->get('/', 'GlobalItemOptionController@getGlobalItemOptionById');
            $api->post('/update', 'GlobalItemOptionController@updateById');
            $api->delete('/', 'GlobalItemOptionController@deleteById');
        });
    });

    //Recovering
    $api->group(['prefix' => '/logs'], function ($api) {
        $api->get('/size', 'LogController@getLogSize');
        $api->get('/', 'LogController@listRecoverableItems');
        $api->post('/recover', 'LogController@recoverRecoverableItems');
        $api->delete('/', 'LogController@flush');
    });

    //Database
    $api->group(['prefix' => '/db'], function ($api) {
        $api->get('/lock', 'DatabaseController@lock');
        $api->post('/recover', 'DatabaseController@recover');

        $api->group(['prefix' => '/backup'], function ($api) {
            $api->get('/list', 'DatabaseController@listBackup');
            $api->get('/', 'DatabaseController@backup');
        });
    });
});
