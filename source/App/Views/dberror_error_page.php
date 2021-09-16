<?php use CD\Core\Request; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr" class="dark-mode headings-family-dm">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <title>Error</title>
    <link type="text/css" href="<?php echo Request::getSiteURL(); ?>assets/css/material-icons.css" rel="stylesheet">
    <link type="text/css" href="<?php echo Request::getSiteURL(); ?>assets/css/preloader.css" rel="stylesheet">
    <link type="text/css" href="<?php echo Request::getSiteURL(); ?>assets/css/app.css" rel="stylesheet">
    <link type="text/css" href="<?php echo Request::getSiteURL(); ?>assets/css/dark-mode.css" rel="stylesheet">
    <link type="text/css" href="<?php echo Request::getSiteURL(); ?>assets/chndrwr/cd.css" rel="stylesheet">
</head>
<body class="layout-boxed layout-sticky-subnav">
<div class="preloader">
    <div class="sk-chase">
        <div class="sk-chase-dot"></div>
        <div class="sk-chase-dot"></div>
        <div class="sk-chase-dot"></div>
        <div class="sk-chase-dot"></div>
        <div class="sk-chase-dot"></div>
        <div class="sk-chase-dot"></div>
    </div>
</div>
<div class="mdk-drawer-layout js-mdk-drawer-layout" data-push data-responsive-width="992px">
    <div class="mdk-drawer-layout__content page-content" style="min-height: unset;">
        <div class="container-fluid page__container flex d-flex align-items-center justify-content-center py-128pt" id="app-body">
            <section class="page-section text-center">
                <h1 class="font-weight-bolder mb-0" style="font-size: 128pt; line-height: 180px">503</h1>
                <h4 class="font-size-32pt mb-2"><i class="text-danger material-icons icon-32pt" style="position: relative; top: -4px">error</i>&nbsp;<em>Internal Server Error</em></h4>
                <strong class="text-50 font-size-16pt">This website is temporarily down.</strong>
            </section>
        </div>
        <div class="js-fix-footer footer border-top-2" id="app-footer">
            <div class="pb-16pt pb-lg-24pt mt-24pt">
                <div class="container page__container">
                    <div class="bg-dark rounded page-section py-lg-32pt px-16pt px-lg-24pt">
                        <div class="row">
                            <div class="col-md-7 col-sm-4 mb-24pt mb-md-0 text-white flex d-flex align-items-center m-0">
                                <img class="brand-icon m-0"
                                     src="<?php echo Request::getSiteURL(); ?>assets/chndrwr/images/cd-landscape-logo.png" width="200"
                                     alt="Chaindrawer logo">
                            </div>
                            <div class="col-md-5 text-md-right">
                                <div class="text-white-50 small mb-12pt">
                                    &copy;
                                    <script>
                                        let date = new Date();
                                        document.write(date.getFullYear().toString());
                                    </script>
                                    Chaindrawer
                                    <br>
                                    All rights reserved.
                                </div>
                                <p class="measure-lead-max mb-0 small">
                                    Chaindrawer is <strong>NOT</strong> affiliated with Axie Infinity.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo Request::getSiteURL(); ?>assets/vendor/jquery.min.js"></script>
<script src="<?php echo Request::getSiteURL(); ?>assets/vendor/bootstrap.min.js"></script>
<script src="<?php echo Request::getSiteURL(); ?>assets/vendor/dom-factory.js"></script>
<script src="<?php echo Request::getSiteURL(); ?>assets/js/app.js"></script>
</body>
</html>
    