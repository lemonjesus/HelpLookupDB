<?php require_once("authlib.php"); ?>
<script>
  $(document).ready(function() {
    $("#submit_class_search").click(function(e) {
      window.location.href = "/search.php?q="+$("#class_search").val();
      e.stopPropagation();
      e.preventDefault();
      false;
    });
  });
</script>
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">Rose Hulman Help Lookup</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="/">Find Help <span class="sr-only">(current)</span></a></li>
        <li><a href="login.php">Offer Help</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Other Resources <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="http://www.rose-hulman.edu/offices-and-services/learning-center.aspx">Learning Center Website</a></li>
            <li><a href="http://www.rose-hulman.edu/offices-and-services/learning-center/services-resources/percopo-tutoring.aspx">Sophomore Tutor Website</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="help.php">Website Help</a></li>
          </ul>
        </li>
      </ul>
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search another class" id="class_search">
        </div>
        <button type="submit" class="btn btn-default" id="submit_class_search">Submit</button>
      </form>
      <ul class="nav navbar-nav navbar-right">
      <?php if(assertLoggedIn()) { ?>
        <li><a href="/logout.php">Logout</a></li>
      <?php } else { ?>
        <li><a href="/login.php">Login</a></li>
      <?php } ?>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
