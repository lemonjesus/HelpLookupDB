<html>
<head>
  <title>Rose Hulman Help Lookup</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    html, body {
      margin: 0px;
      padding: 0px;
      overflow: hidden;
      color: white;
      font-family: sans-serif;
    }
    #background {
      background-image: url(img/index_background.jpg);
      background-size: cover;
      position: absolute;
      top: 0px;
      left: 0px;
      width: 100%;
      height: 100%;
      padding: 30px;
      filter: blur(5px) brightness(0.7);
    }
    #content {
      position: absolute;
      top: 0px;
      left: 0px;
      padding: 30px;
    }
    h1 {
      font-size: 600%;
    }
    #faketextbox {
      width:100%;
      background-color:white;
      border: 3px black solid;
      border-radius: 10px;
      font-size:500%;
      padding: 10px;
    }
    #entered {
      color: black;
    }
    #suggestion {
      color: grey;
      margin-left: -20px;
    }
    @media (max-width: 640px) {
      #content {
        padding: 0px;
      }
      h1 {
        font-size: 300%;
      }
      h2 {
        font-size: 200%;
      }
      #faketextbox {
        font-size: 100%;
        width: 100%;
      }
      #suggestion {
        margin-left: -4px;
      }
    }
  </style>

  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function(){
      $(document).keydown(function(e){
        if(e.ctrlKey) return;
        if(e.keyCode==8) {
          e.preventDefault();
          e.stopPropagation();
          $("#entered").text($("#entered").text().substring(0,$("#entered").text().length-1));
          if($("#entered").text()=="") {
            $("#entered").html("&nbsp;");
            $("#suggestion").text("");
          } else {
            triggerSuggestion();
          }
        } else if(e.keyCode==39) {
          $("#entered").text($("#entered").text()+$("#suggestion").text());
          $("#suggestion").text("");
        } else if(e.keyCode==13) {
          executeSearch();
        } else if(e.keyCode==32) {
          $("#entered").html($("#entered").text()+" ");
        } else if(e.key.length==1){
          if($("#entered").html()=="&nbsp;") $("#entered").html("");
          $("#entered").text($("#entered").text()+e.key);
          triggerSuggestion();
        }
      });
    });
    function triggerSuggestion() {
      $.get("/api/autocomplete.php?q="+$("#entered").text(),function(data){
        $("#suggestion").text(data);
      });
    }
    function executeSearch() {
      window.location.href = "/search.php?q="+encodeURIComponent($("#entered").text());
    }
  </script>
</head>
<body>
  <div class="fluid-container" id="background">
  </div>
  <div class="fluid-container" id="content">
    <div class="row">
      <div class="col-lg-12">
        <h1>You're not stupid. You just need help.</h1>
        <h2>Start typing the name or number of a course find people who can help you.</h2>
        <div id="faketextbox">
          <span id="entered"></span>
          <span id="suggestion">&nbsp;</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
