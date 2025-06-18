<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <link rel=icon href=/images/eweb.webp>
    <link rel="stylesheet" href="/css/master.css">

    <title>Khanna Feed Limited</title>
  </head>

  <body class="text-left">
    <noscript>
      <strong>
        Popular Cattle Feed Manufacturers in Khanna
        </strong
      >
    </noscript>

    <!-- built files will be auto injected -->
    <div class="loading_wrap" id="loading_wrap">
      <div class="loader_logo">
      <img src="/images/eweb.webp" class="" alt="logo" />

      </div>

      <div class="loading"></div>
    </div>
    <div id="login">
        <login-component></login-component>
      </div>

      <script>
        window.config = {
          "ModulesEnabled" : @json($ModulesEnabled),
          "ModulesInstalled" : @json($ModulesInstalled),
        };
      </script>

      <script src="/js/login.min.js?v=4.0.8"></script>
  </body>
</html>

    