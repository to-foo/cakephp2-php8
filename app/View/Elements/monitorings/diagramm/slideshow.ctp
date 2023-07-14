<?php
if(!isset($this->request->data['lineplot'])) return;
if(count($this->request->data['lineplot']) == 0) return;
?>

<div class="slideshow-container">

<?php

$x = 1;

foreach ($this->request->data['lineplot'] as $key => $value) {

  if(!isset($value['diagramm'])) continue;
  if(!isset($value['id'])) continue;

  echo '<div class="mySlides fade image_numer_'. $x .' image_' . $value['id'] . '">';
  echo '<div class="numbertext">1 / 3</div>';
  echo '<img id="' . $value['id'] . '" class="" src="data:image/png;base64, ' . $value['diagramm']  . ' " />';
  echo '<div class="text"> </div>';
  echo '</div>';

  $x++;

}
?>

  <!-- Next and previous buttons -->
  <a class="prev">&#10094;</a>
  <a class="next">&#10095;</a>
</div>
<br>

<style>

.progress_legend {
  margin: 0 1em;
}

/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: 1em 1em 1em 0;
  border: solid 2px #dcdcdc;
  border-radius: 3px;

}

/* Hide the images by default */
.mySlides {
  display: none;
}

/* Next & previous buttons */
.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  margin-top: -22px;
  padding: 16px;
  color: white;
  font-weight: bold;
  font-size: 18px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
  background-color: rgba(0,0,0,0.8);
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover, .next:hover {
  background-color: rgba(0,0,0,0.8);
}

/* Caption text */
.text {
  color: #000000;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  cursor: pointer;
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active, .dot:hover {
  background-color: #717171;
}

/* Fading animation */
.fade {
  -webkit-animation-name: fade;
  -webkit-animation-duration: 1.5s;
  animation-name: fade;
  animation-duration: 1.5s;
}

@-webkit-keyframes fade {
  from {opacity: .4}
  to {opacity: 1}
}

@keyframes fade {
  from {opacity: .4}
  to {opacity: 1}
}

</style>

<script type="text/javascript">

$(document).ready(function() {

var slideIndex = 1;
//showSlides(slideIndex);

$("div.mySlides").hide();
$("div.image_numer_1").show();

$("div.slideshow-container a.next").click(function() {

  slideIndex += 1;

  if($("div.image_numer_" + slideIndex).length == 0){

    slideIndex = 1;
    return false;

  }

  $("div.mySlides").hide();
  $("div.image_numer_" + slideIndex).show();

});

$("div.slideshow-container a.prev").click(function() {

  slideIndex -= 1;

  if($("div.image_numer_" + slideIndex).length == 0){

    slideIndex = 1;
    return false;

  }

  $("div.mySlides").hide();
  $("div.image_numer_" + slideIndex).show();

});
/*
// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}
*/
/*
function showSlides(n) {

  var i;
  var slides = document.getElementsByClassName("mySlides");

  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}

  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }

  slides[slideIndex-1].style.display = "block";
}
*/
});
</script>
