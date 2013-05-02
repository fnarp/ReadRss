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
      <link rel="stylesheet" href="templates/backend/style/style.css">

      <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
   </head>
   <body>
      <div id="page">
         <header id="header">
            <h1 class="sitetitle">ReadRSS</h1>
         </header>
         <aside id="sidebar">
            <section class="widget">
               <form role="search" method="POST" action="?action=search" class="search-form">
                  <input type="search" placeholder="{trl;SearchPlaceholder}" />
               </form>
            </section>
            <section class="widget">
               <nav id="main-nav">
                  <ul class="navigation">
                     <li class="nav-item"><a href="">{trl;MenuNew}</a></li>
                     <li class="nav-item"><a href="?show=starred">{trl;MenuStarred}</a></li>
                     <li class="nav-item"><a href="?show=archive">{trl;MenuArchive}</a></li>
                     <li class="nav-item">
                        <a href="?show=tags">{trl;MenuTags}</a>
                        <ul class="tag-list">
                           {ctr;PageController;showTags;}
                        </ul>
                     </li>
                  </ul>
                  <div class="small-clear"></div>
               </nav>
            </section>
            <section class="widget">
               <div id="control-nav">
                  <ul class="controls">
                     <li class="control">
                        <a class="rss" title="{trl;ShowRssFeeds}" href="?action=feeds">
                           {trl;ShowRssFeeds}
                        </a>
                     </li>
                     <li class="control">
                        <a class="signout" title="{trl;SignOut}" href="?action=logout">
                            {trl;SignOut}
                        </a>
                     </li>
                  </ul>
                  <div class="clear"></div>
                </div>
            </section>
         </aside>
         <div id="content">
            {ctr;PageController;showContent;}
            <div class="clear"></div>
         </div>
      </div>
   </body>
</html>