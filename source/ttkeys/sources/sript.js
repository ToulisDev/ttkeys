// Navigation Dropdown Menu

function toggleNavMenu(elementId) {
  document.getElementById(elementId).classList.toggle("active");
}

window.onresize = function() {
  if (window.innerWidth > 768) {
    var navMenu = document.getElementById("nav-menu");
    if (navMenu != null)
    {
      if (navMenu.classList.contains("active")) {
        navMenu.classList.remove("active");
      }
    }
  }
  if (window.innerWidth <= 768) {
    var navMenu = document.getElementById("nav-fullmenu");
    if (navMenu != null)
    {
      if (navMenu.classList.contains("active")) {
        navMenu.classList.remove("active");
      }
    }
  }
};


// Slide Functionality
var slideIndex = 0;
if (document.getElementsByClassName("featured-slider-item").length > 1)
{
  carousel();
  var timer = setInterval(carousel, 5000); 
}
var slider = document.getElementById("featured-slider");
if (slider != null)
{
  slider.addEventListener("mouseover", () => {
    clearInterval(timer);
  });
  
  slider.addEventListener("mouseout", () => {
    timer = setInterval(carousel, 5000);
  });
}
if (document.getElementsByClassName("featured-slider-navigator-bar").length > 0) {
  var sliders = document.getElementsByClassName("featured-slider-navigator-bar");
  for (var i = 0; i < sliders.length; i++ )
  {
    const num = i+1;
    sliders[i].addEventListener("click" , () => {
      carousel(num);
    });
  }
}

function carousel(index)
{
  var x = document.getElementsByClassName("featured-slider-item");
  var y = document.getElementsByClassName("featured-slider-navigator-bar");
  if (x < 1)
  {return;}
  for (var i = 0; i < x.length; i++) {
    if (x[i].classList.contains("active"))
    {
      x[i].classList.remove("active");
    }
  }
  for (var i = 0; i < y.length; i++) {
    if (y[i].classList.contains("active"))
    {
      y[i].classList.remove("active");
    }
  }
  if (index)
  {
    slideIndex = index;
  } else {
    slideIndex++;
  }
  if (slideIndex > x.length) {slideIndex = 1}
  x[slideIndex-1].classList.add("active");
  if (y.length > 0) {
    y[slideIndex-1].classList.add("active");
  }
}


// Cart Functionality
function addToCart(product_id) {
  if (product_id == null)
  {
    console.error("No product_id passed");
    return;
  }
  const cartAmount = document.getElementById('cart-icon');
  const addBtn = document.getElementById('btn-add-cart');
  const checkoutBtn = document.getElementById('btn-checkout');
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "cart.php");
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      if (addBtn)
      {addBtn.classList.remove('active');}
      if (checkoutBtn)
      {checkoutBtn.classList.add('active');}
      cartAmount.setAttribute("value", parseInt(cartAmount.getAttribute("value")) + 1);
      console.log("Response:", xhr.response);
      console.log("Successfully added to cart");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "product_id=" + encodeURIComponent(product_id);
  xhr.send(data);
  
}

function deleteFromCart(product_id) {
  if (product_id == null)
  {
    console.error("No product_id passed");
    return;
  }
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "cart.php");
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      console.log("Response:", xhr.response);
      console.log("Successfully added to cart");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "del_product_id=" + encodeURIComponent(product_id);
  xhr.send(data);
  window.location.reload();
}

function cartPage() {
  window.open('./cart.php', '_self');
}

function checkoutPage() {
  window.open('./checkout.php', '_self');
}

function likeProduct(product_id) {
  if (product_id == null)
  {
    console.error("No product_id passed");
    return;
  }
  var url = "product.php?product_id="+String(product_id);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      console.log("Response:", xhr.response);
      console.log("Successfully got response");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "like=" + encodeURIComponent(product_id);
  xhr.send(data);
  window.location.reload();
}

function dislikeProduct(product_id) {
  if (product_id == null)
  {
    console.error("No product_id passed");
    return;
  }
  var url = "product.php?product_id="+String(product_id);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      console.log("Response:", xhr.response);
      console.log("Successfully got response");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "dislike=" + encodeURIComponent(product_id);
  xhr.send(data);
  window.location.reload();
}

function wishlistProduct(product_id) {
  if (product_id == null)
  {
    console.error("No product_id passed");
    return;
  }
  var url = "product.php?product_id="+String(product_id);
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      console.log("Response:", xhr.response);
      console.log("Successfully got response");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "wishlist=" + encodeURIComponent(product_id);
  xhr.send(data);
  window.location.reload();
}

function cancelOrder(order_id) {
  if (order_id == null)
  {
    console.error("No order_id passed");
    return;
  }
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "myorders.php");
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status == 200) {
      console.log("Response:", xhr.response);
      console.log("Successfully got response");
    } else {
      console.error("An error occurred");
    }
  };

  var data = "order_id=" + encodeURIComponent(order_id);
  xhr.send(data);
  window.location.reload();
}