jQuery(document).ready(function() {

  // added function to clean up Mega Menu functionality -Abe
  function menuLoader() {
    var megaToggle = jQuery('#mega_menu_toggle')[0],
      megaMenu = jQuery('#mega_menu')[0],
      headerNav = jQuery('#header_nav')[0];
      // mega menu
    jQuery('#mega_menu_toggle').on('click', function(e) {
     e.preventDefault();
      if (megaMenu.style.display === 'none' || megaMenu.style.display === '') {
        megaMenu.style.display = 'block';
        (headerNav, megaToggle).classList.add('open');
      } else {
        megaMenu.style.display = 'none';
        (headerNav, megaToggle).classList.remove('open');
      }
    });
  }
  
    //make parent div of front-page call-to-action button clickable
    jQuery(".front-page-classic .first-content p:last-of-type").click(function(){
      window.location=jQuery(this).find("a").attr("href"); 
      return false;
  });

  // find the size of the header logo and assign classes accordingly
  var logo = jQuery('.cu2017_logo');
  if (logo.width() > 200) {
    logo.addClass('schools_and_colleges_logo');
  }
  // updates header opacity if there is a header background image or front page slider
  // if header image is present, change header opacity to 85% and insert the header image before layout-content
  if (jQuery('#block-headerbackgroundimage')[0] && jQuery(window).width() >= 1251) {
    jQuery('header').addClass('header_opacity');
  }
  // if front page slider is present and window width > 768, change header opcacity to 85%
  if (jQuery(window).width() >= 1251 && jQuery('.main_front')[0]) {
    jQuery('header').addClass('header_opacity');
  }

  // collapse schools and colleges and information for menus at <769px
  if (jQuery(window).width() < 769) {
    // collapse 9 schools and colleges menu and information for menu onload
    jQuery('.two_col_menu').addClass('hidden');
    // toggle hidden class to show and hide menus onclick
    jQuery('#schools_and_colleges_menu').on("click", function() {
      jQuery('#schools_and_colleges_menu_body').toggleClass('hidden');
      jQuery(this).toggleClass('rotate_arrow');
    });
    jQuery('#block-informationfor-menu').on("click", function() {
      jQuery('#information_for_menu_body').toggleClass('hidden');
      jQuery(this).toggleClass('rotate_arrow');
    });
  }
  // Funnelback
  if (jQuery(window).width() >= 769) {
    jQuery('header input.cu-query').autocompletion({
      program: 'https://search.creighton.edu/s/suggest.json',
      alpha: '0.5',
      show: '10',
      sort: '0',
      length: '3',
      datasets: {
        organic: {
          name: 'Suggestions',
          collection: 'creighton-search',
          profile: '_default',
          show: '10'
        }
      }
    });
  }
  // updates font size if the width of the email address in the contact box
  // exceeds contact box width
  // TODO: modify to loop through multiple email addresses
  // and also to handle multiple contact boxes?
  if (jQuery('.contact_us')[0]) {
    var contact = jQuery('.contact_us');
    var email = jQuery('.contact_us p a');
    var font_size;
    email[0].style.fontSize == '' ? font_size = 16 : font_size = parseInt(email[0].style.fontSize.slice(0, -2));

    while (contact.width() <= (email.width() + 5)) {
      font_size--;
      email[0].style.fontSize = font_size.toString() + 'px';
    }
  }
  // making menu links open in new window if the links leave the current site
  function newWindow($menuSelector) {
    var currentDomain = document.domain;
    jQuery($menuSelector).each(function() {
      var link = jQuery(this).attr('href');
      if (link.indexOf(":") > -1) {
        var linkDomain = link.split("/");
        linkDomain = linkDomain[2]; // set variable to the 3rd element from the array created by split
      } else(linkDomain = currentDomain);
      if (linkDomain != currentDomain) {
        jQuery(this).attr("target", "_blank");
      }
    });
  }
  newWindow('ul.menu--transaction-menu li a');
  newWindow('ul.header_nav li a');
  newWindow('ul#schools_and_colleges_menu_body  li a');
  newWindow('ul#information_for_menu_body li a');
  menuLoader();

  // for long emails
  jQuery(".right_sidebar .field--name-body p a").wrap("<div class='fitty'></div>");

  // Let people use fitty without updating this file
  fitty(".fitty", {
    minSize: 8,
    maxSize: 17,
    multiLine: true
  });
});
