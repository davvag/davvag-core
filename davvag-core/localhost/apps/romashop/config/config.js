WEBDOCK.component().configure(function(exports){

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }

    //var location = JSON.parse(unescape(getCookie("Location")));

    var routes = {
        home : "/home",
        notFound: "/notFound",
        location: location,
        partials : {
            "/" : "partial-home",
            "/home" : "partial-home",
            "/mobilewelcome":"partial-mobile-welcome",
            "/mobilelogin":"partial-mobile-login",
            "/notFound" : "partial-404",
            "/default" : "partial-home",
            "/about" : "partial-about",
            "/contact" : "partial-contact",
            "/cart" : "partial-cart",
            "/checkout" : "partial-cart-checkout",
            "/paycomplete" : "partial-cart-paycomplete",
            "/payerror":"partial-cart-payerror",
            "/paysuccess":"partial-cart-payerror",
            "/payment" : "partial-cart-payment",
            "/services":"partial-services",
            "/user":"partial-user",
            "/regsuccess":"partial-user-register-success",
            "/item":"partial-item",
            "/products" : "partial-products",
            "/admin" : "partial-admin",
            "/admin-allproducts" : "partial-admin-product-all",
            "/admin-productform" : "partial-admin-product-form",
            "/admin-allstores" : "partial-admin-stores-all",
            "/admin-storesform" : "partial-admin-stores-form",
            "/admin-allriders" : "partial-admin-riders-all",
            "/admin-ridersform" : "partial-admin-riders-form",
            "/admin-productmapper" : "partial-admin-productmapper",
            "/riderorders" : "partial-rider-orders",
            "/admin-allcategories" : "partial-admin-category-all",
            "/admin-categoryform" : "partial-admin-category-form",
            "/admin-uom" : "partial-admin-uom-all",
            "/admin-uomform" : "partial-admin-uom-form",
            "/admin-grn" : "partial-admin-grn-all",
            "/admin-grnform" : "partial-admin-grn-form",
            "/admin-inventory" : "partial-admin-inventory",
            "/admin-orders" : "partial-admin-order-all",
            "/admin-orders-nextday" : "partial-admin-order-nextday",
            "/rider-login" : "partial-rider-login",
            "/forgetpassword" : "partial-forget-password",
            "/resetpassword" : "partial-reset-password"
        }
    };

    exports.getSettings = function(){
        return routes;
    }
    exports.getShellComponent("soss-routes").set (routes);
});