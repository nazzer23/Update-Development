$(document).ready(function () {
	
	function checkSession() {
        $.ajax({
            url: "https://api.updatesocial.co.uk/user/session/checkSession.php",
            method: "POST",
            context: this,
            dataType: "json",
            data: {session:localStorage.getItem("sessionString"), userID:localStorage.getItem("userID")},
            success: function (data) {
                if(data.status == true) {
                    $.ajax({
                        url: "/core/handlers/user/session.php",
                        method: "POST",
                        context: this,
                        dataType: "json",
                        data: {session:localStorage.getItem("sessionString"), userID:localStorage.getItem("userID")},
                        success: function (data) {
                            if(data.status == false) {
                                window.location = "/logout.php";
                            }
                        }
                    });
                }
            }
        });
    }
	
    $("#loginBtn").on('click', function(e) {
        e.preventDefault();
        var strEmail = $("#strEmail").val();
        var strPassword = $("#strPassword").val();
        $.ajax({
            url: "https://api.updatesocial.co.uk/user/login.php",
            method: "POST",
            context: this,
            dataType: "json",
            data: {strEmail:strEmail, strPassword:strPassword},            
            success: function (data) {
                if(data.status == true){
                    if (typeof(Storage) !== "undefined") {
                        localStorage.setItem("sessionString", data.sessionString);
                        localStorage.setItem("userID", data.userID);
						checkSession();
						window.location = "/";
                    }
                } else {
                    $("#errorBox").html('<div class="alert alert-danger" role="alert">'+ data.message +'</div>').hide();
                    $("#errorBox").slideDown();
                }
            }
        });
    });
});