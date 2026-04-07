var sheet = document.createElement('style'),
      $rangeInput = $('.range input'),
      prefs = ['webkit-slider-runnable-track', 'moz-range-track', 'ms-track'];

   document.body.appendChild(sheet);

   var getTrackStyle = function(el) {
      var curVal = el.value,
         val = curVal * 12.5,
         style = '';

      // Set active label
    //   $('.range-labels span').removeClass('active selected');

    //   var curLabel = $('.range-labels').find('span:nth-child(' + curVal + ')');

    //   curLabel.addClass('active selected');
    //   curLabel.prevAll().addClass('selected');

      // Change background gradient
      for (var i = 1; i < prefs.length; i++) {
         style += '.range {background: linear-gradient(to right, #f7863b 0%, #f7863b ' + val + '%, #fff ' + val +
            '%, #fff 100%)}';
         style += '.range input::-' + prefs[i] + '{background: linear-gradient(to right, #f7863b 0%, #f7863b ' +
            val + '%, #b2b2b2 ' + val + '%, #b2b2b2 100%)}';
      }

      return style;
   }

//    $rangeInput.on('input', function() {
//       sheet.textContent = getTrackStyle(this);
//    });

   // Change input value on label click
   $('#ramInput span').on('click', function() {
      var index = $(this).index();

      $rangeInput.val(index).trigger('input');

   });