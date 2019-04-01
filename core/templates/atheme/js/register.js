$(document).ready(function () {

    $("#registerBtn").on('click', function(e) {
        e.preventDefault();
        var strEmail = $("#strEmail").val();
        var strPassword = $("#strPassword").val();
        var strConfirmPassword = $("#strConfirmPassword").val();
        var strFirstName = $("#strFirstName").val();
        var strLastName = $("#strLastName").val();
        var dob = $("#dob").val();
        var strGender = $("#strGenderM").val() ? $("#strGenderM").val() : $("#strGenderF").val();

        
        $.ajax({
            url: "https://api.updatesocial.co.uk/user/register.php",
            method: "POST",
            context: this,
            dataType: "json",
            data: {
                strEmail:strEmail,
                strPassword:strPassword,
                strFirstName:strFirstName,
                strLastName:strLastName,
                strConfirmPassword:strConfirmPassword,
                dob:dob,
                strGender:strGender
            },            
            success: function (data) {
                if(data.status == true){
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
                                }
                            } else {
                                $("#messageBox").html('<div class="alert alert-danger" role="alert">'+ replaceAll(data.message,"\n","<br>") +'</div>').hide();
                                $("#messageBox").slideDown();
                            }
                        }
                    });
                }
                var type = data.status ? "success" : "danger";
                $("#messageBox").html('<div class="alert alert-'+type+'" role="alert">'+ replaceAll(data.message,"\n","<br>") +'</div>').hide();
                $("#messageBox").slideDown();
            }
        });
    });

    function checkSession() {
        $.ajax({
            url: "https://api.updatesocial.co.uk/user/session/checkSession.php",
            method: "POST",
            context: this,
            dataType: "json",
            data: {session:localStorage.getItem("sessionString"), userID:localStorage.getItem("userID")},
            success: function (data) {
                if(data.status) {
                    $.ajax({
                        url: "/core/handlers/user/session.php",
                        method: "POST",
                        context: this,
                        dataType: "json",
                        data: {session:localStorage.getItem("sessionString"), userID:localStorage.getItem("userID")},
                        success: function (data) {
                            if(data.status) {
                                window.location = "/index.php";
                            }
                        }
                    });
                } else {
                    window.localStorage.clear();
                    window.location = "/";
                }
            }
        });
	}
	
	function escapeRegExp(string){
		return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	}
	
	function replaceAll(str, term, replacement) {
		return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
	}

});