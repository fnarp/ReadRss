<!DOCTYPE html>
<html>
   <head>
      <title>{var;sitetitle}</title>

      <meta charset="utf-8">

      <meta name="description" content="{var;description}">
      <meta name="keywords" content="{var;keywords}">

      <base href="{var;basepath}">

      <meta name="viewport" content="width=device-width, initial-scale=1">

      <link rel="icon" href="templates/favico.ico" type="image/x-icon">
      <link rel="stylesheet" href="templates/frontend/style/style.css">

      <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
   </head>
   <body>
      <div id="page">
         <header id="header">
            <h1 class="sitetitle">ReadRSS</h1>
         </header>
         <div class="loginbox" >
            <form method="POST" action="index.php?action=signin">
               <input type="email" name="user" value="{var;user}" placeholder="example@example.ch" autofocus required />
               <input type="password" name="password" placeholder="helloworld" required />
               <input type="submit" value="Sign in">
            </form>
            <div class="errorMsg">
               {var;error}
            </div>
         </div>
      </div>
   </body>
</html>