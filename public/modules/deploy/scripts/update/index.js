$(function() {
    $("a.update-btn").click(function() {
        var form = $(this).parent('form');
        var targetVersion = form.find(".target-version").val();
        var envID = form.attr("data-id");

        if (!targetVersion || !envID) {
            alert("Enter target version");
            return false;
        }

        $.post(
            config.url.addUpdateTask,
            {envID: envID, targetVersion: targetVersion},
            function (resp) {
                if (resp.msg) {
                    if (resp.status) document.location.reload();
                    else alert(resp.msg);
                }
            }
        );
        return false;
    });

    $("a.verify-btn").click(function() {
        var versionID = $(this).attr('data-version-id');
        var button = $(this);

        if (!versionID) {
            alert('Verification not available');
            return;
        }

        $.post(
            config.url.verify,
            {'version-id': versionID},
            function(resp) {
                button
                    .off()
                    .removeClass('verify-btn')
                    .addClass('btn-success')
                    .addClass('disabled')
                    .text('Version verified!')
            }
        )

    });

    $("select#tags").change(function() {
        $("#target-version").val($(this).val());
    });

    var stateTimer;

    if ($(".waiting-update").length) {
        stateTimer = setInterval(updateEnvState, config.checkEnvStateInterval);
    }

    function updateEnvState() {

        $(".waiting-update").each(function() {
            var envID = $(this).find('form').attr("data-id");
            var env = $(this);

            $.post(
                config.url.getEnvironmentState,
                {id : envID},
                function (resp) {

                    if (resp.state == 'locked') {
                        var progressBar = env.find('form').find('div.progress');
                        progressBar.addClass('active');
                        progressBar.find('.message-waiting').hide();
                        progressBar.find('.message-locked').show();
                    } else if (resp.state == 'idle' && env.hasClass('waiting-update')) {
                        clearInterval(stateTimer);
                        document.location.reload();
                    }
                }
            );
        });


    }

});