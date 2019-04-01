$(document).ready(function () {
    console.log("Update is ready.");

    if(localStorage.getItem("sessionString") != null && localStorage.getItem("userID") != null) {
        console.log("[UPDATE] User is logged in. Initiated Session Checker Schedule");
        checkSession();
        setInterval(
            function() {
                checkSession();
            },
            1000
        );
    }

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

    if(localStorage.getItem("sessionString") != null) {
        function getPostData() {
            var id = $(this).attr('postID');

            $.ajax({
                url: "core/handlers/posts/likePost.php",
                method: "POST",
                context: this,
                dataType: "json",
                data: {postID:id},            
                success: function (data) {
                    var liked = data.likedPost;
                    console.log(liked);
                    if(liked) {
                        $(this).text("Unlike");
                    } else {
                        $(this).text("Like");
                    }
                }
            });
        }

        $(document).on('click','#likePost',getPostData);

        checkNotifications();

        function checkNotifications() {
            $.ajax({
                url: "core/handlers/user/getNotificationsNumber.php",
                dataType: "json",
                success: function (data) {
                    const {notifCount, msgCount, friendCount} = data;
                    $("#requestCount").text(friendCount);
                    $("#messageCount").text(msgCount);
                    $("#notifCount").text(notifCount);
                }
            });
        }

        setInterval(
            function() {
                checkNotifications();
            },
            5000
        );
    }

});