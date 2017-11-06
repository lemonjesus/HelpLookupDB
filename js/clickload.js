//ClickLoad Library
//Version 1, December 2015
//Written by Tucker Osman
//github.com/osmiumusa/ClickLoad/

(function($) {
  $.fn.clickload = function(options) {
    $(this).click(function() {
      if (options.animate !== false) {
        $(this).css("-webkit-animation", "progress-bar-stripes 2s linear infinite");
        $(this).css("-o-animation", "progress-bar-stripes 2s linear infinite");
        $(this).css("animation", "progress-bar-stripes 2s linear infinite");
        $(this).addClass("progress-bar-striped");
      }
      $(this).removeClass("btn-success btn-danger");
      if (options.during) $(this).text(options.during);
      if (options.disable !== false) $(this).prop("disabled", true);
      var thisbutton = $(this);
      $.ajax({
        url: options.url,
        method: options.method || "GET",
        headers: options.headers || {},
        data: (function() {
            if(options.data instanceof Function) return options.data();
            else options.data || {}
          })(),
        success: function(data, textStatus, jqXHR) {
          //User supplied function returns false if actually a success
          thisbutton.prop("disabled", false);
          thisbutton.css("-webkit-animation", "");
          thisbutton.css("-o-animation", "");
          thisbutton.css("animation", "");
          if (!options.success || options.success(data, textStatus, jqXHR) !== false) {
            thisbutton.text(options.after);
            thisbutton.removeClass("progress-bar-striped").addClass("btn-success");
          } else {
            if (options.error) {
              thisbutton.text(options.error(jqXHR, textStatus, undefined, data));
            } else thisbutton.text("Error!");
            thisbutton.removeClass("btn-success").addClass("btn-danger");
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          thisbutton.css("-webkit-animation", "");
          thisbutton.css("-o-animation", "");
          thisbutton.css("animation", "");
          thisbutton.removeClass("progress-bar-striped").removeClass("btn-success").addClass("btn-danger");
          var returned = (options.error) ? options.error(jqXHR, textStatus, errorThrown, undefined) : "Error!";
          thisbutton.text(returned);
        }
      })
    });
  };
}(jQuery));
