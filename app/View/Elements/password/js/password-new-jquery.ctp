 <script type="text/javascript">

 /*
  * jQuery Minimun Password Requirements 1.1
  * http://elationbase.com
  * Copyright 2014, elationbase
  * Check Minimun Password Requirements
  * Free to use under the MIT license.
  * http://www.opensource.org/licenses/mit-license.php
  */


(function($) {
  $.fn.extend({
    passwordRequirements: function(options) {

      // options for the plugin
      var defaults = {
        Message: "Password strength conditions",
        NumbersCountText: "Number of letters",
        LowercaseText: "Lowercase letter",
        UppercaseText: "Capital letter",
        NumbersText: "Numbers",
        SpecialText: "Special character",
        numCharacters: 9,
        useLowercase: 1,
        useUppercase: 1,
        useNumbers: 1,
        useSpecial: 1,
        infoMessage: '',
        style: "light", // Style Options light or dark
        fadeTime: 300 // FadeIn / FadeOut in milliseconds
      };

      options = $.extend(defaults, options);

      return this.each(function() {

        var o = options;
				//console.log(o.numCharacters);

        o.infoMessage = o.Message;
        //        o.infoMessage = 'The minimum password length is ' + o.numCharacters + ' characters and must contain at least 1 lowercase letter, 1 capital letter, 1 number, and 1 special character.';
        // Add Variables for the li elements
        var numCharactersUI = '<li class="pr-numCharacters"><span></span>' + o.NumbersCountText + ': ' + o.numCharacters + '</li>',
          useLowercaseUI = '',
          useUppercaseUI = '',
          useNumbersUI = '',
          useSpecialUI = '';
        // Check if the options are checked
        if (o.useLowercase == 1) {
          useLowercaseUI = '<li class="pr-useLowercase"><span></span>' + o.LowercaseText + '</li>';
        }
        if (o.useUppercase == 1) {
          useUppercaseUI = '<li class="pr-useUppercase"><span></span>' + o.UppercaseText + '</li>';
        }
        if (o.useNumbers == 1) {
          useNumbersUI = '<li class="pr-useNumbers"><span></span>' + o.NumbersText + '</li>';
        }
        if (o.useSpecial == 1) {
          useSpecialUI = '<li class="pr-useSpecial"><span></span>' + o.SpecialText + '</li>';
        }

        // Append password hint div
        var messageDiv = '<div id="pr-box"><i></i><div id="pr-box-inner"><p>' + o.infoMessage + '</p><ul>' + numCharactersUI + useLowercaseUI + useUppercaseUI + useNumbersUI + useSpecialUI + '</ul></div></div>';

        // Set campletion vatiables
        var numCharactersDone = true,
          useLowercaseDone = true,
          useUppercaseDone = true,
          useNumbersDone = true,
          useSpecialDone = true;

        // Show Message reusable function
        var showMessage = function() {

          if (numCharactersDone === false || useLowercaseDone === false || useUppercaseDone === false || useNumbersDone === false || useSpecialDone === false) {
            $(".pr-password").each(function() {
              // Find the position of element
              var posH = $(this).offset().top,
                itemH = $(this).innerHeight(),
                totalH = posH + itemH,
                itemL = $(this).offset().left;
              // Append info box tho the body
              $("body").append(messageDiv);
              $("#pr-box").addClass(o.style)
                .fadeIn(o.fadeTime)
                .css({
                  top: totalH,
                  left: itemL
                });
            });
          }
        };

        // Show password hint
        $(this).on("focus", function() {
          showMessage();
        });

        // Delete Message reusable function
        var deleteMessage = function() {
          var targetMessage = $("#pr-box");
          targetMessage.fadeOut(o.fadeTime, function() {
            $(this).remove();
          });
        };

        // Show / Delete Message when completed requirements function
        var checkCompleted = function() {
          if (numCharactersDone === true && useLowercaseDone === true && useUppercaseDone === true && useNumbersDone === true && useSpecialDone === true) {
            if ($(".pr-password").val().length > 8) {

              $(".pr-password").next("span").removeAttr("class");
              $(".pr-password").next("span").addClass("success");

            } else {

              $(".pr-password").next("span").removeAttr("class");
              $(".pr-password").next("span").addClass("error");
              $('form.login input[type="submit"]').prop("disabled", "disabled");

            }

            deleteMessage();
          } else {

            showMessage();
            $(".pr-password").next("span").removeAttr("class");
            $(".pr-password").next("span").addClass("error");
            $('form.login input[type="submit"]').prop("disabled", "disabled");

          }
        };

        // Show password hint
        $(this).on("blur", function() {
          if (numCharactersDone === true && useLowercaseDone === true && useUppercaseDone === true && useNumbersDone === true && useSpecialDone === true) {

            $(".pr-password").next("span").removeAttr("class");
            $(".pr-password").next("span").addClass("success");

          } else {

            $(".pr-password").next("span").removeAttr("class");
            $(".pr-password").next("span").addClass("error");
            $('form.login input[type="submit"]').prop("disabled", "disabled");

          }

          deleteMessage();
        });


        // Show or Hide password hint based on user's event
        // Set variables
        var lowerCase = new RegExp('[a-z]'),
          upperCase = new RegExp('[A-Z]'),
          numbers = new RegExp('[0-9]'),
          specialCharacter = new RegExp('[!,%,&,@,#,$,^,*,?,_,~]');

        // Show or Hide password hint based on keyup
        $(this).on("keyup focus", function() {
          var thisVal = $(this).val();

          checkCompleted();

          // Check # of characters
          if (thisVal.length >= o.numCharacters) {
            // console.log("good numCharacters");
            $(".pr-numCharacters span").addClass("pr-ok");
            numCharactersDone = true;
          } else {
            // console.log("bad numCharacters");
            $(".pr-numCharacters span").removeClass("pr-ok");
            numCharactersDone = false;
          }
          // lowerCase meet requirements
          if (o.useLowercase == 1) {
            if (thisVal.match(lowerCase)) {
              // console.log("good lowerCase");
              $(".pr-useLowercase span").addClass("pr-ok");
              useLowercaseDone = true;
            } else {
              // console.log("bad lowerCase");
              $(".pr-useLowercase span").removeClass("pr-ok");
              useLowercaseDone = false;
            }
          }
          // upperCase meet requirements
          if (o.useUppercase == 1) {
            if (thisVal.match(upperCase)) {
              // console.log("good upperCase");
              $(".pr-useUppercase span").addClass("pr-ok");
              useUppercaseDone = true;
            } else {
              // console.log("bad upperCase");
              $(".pr-useUppercase span").removeClass("pr-ok");
              useUppercaseDone = false;
            }
          }
          // upperCase meet requirements
          if (o.useNumbers == 1) {
            if (thisVal.match(numbers)) {
              // console.log("good numbers");
              $(".pr-useNumbers span").addClass("pr-ok");
              useNumbersDone = true;
            } else {
              // console.log("bad numbers");
              $(".pr-useNumbers span").removeClass("pr-ok");
              useNumbersDone = false;
            }
          }
          // upperCase meet requirements
          if (o.useSpecial == 1) {
            if (thisVal.match(specialCharacter)) {
              // console.log("good specialCharacter");
              $(".pr-useSpecial span").addClass("pr-ok");
              useSpecialDone = true;
            } else {
              // console.log("bad specialCharacter");
              $(".pr-useSpecial span").removeClass("pr-ok");
              useSpecialDone = false;
            }
          }
        });
      });
    }
  });
})(jQuery);
</script>
