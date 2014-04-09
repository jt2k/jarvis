<?php
require '../bootstrap.php';
if (isset($jarvis_config['name']) && $jarvis_config['name']) {
    $jarvis = htmlspecialchars($jarvis_config['name']);
} else {
    $jarvis = "jarvis";
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $jarvis ?> console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    html { height: 100%}
    body {font-family: helvetica, arial, sans-serif; font-size: 10pt; margin: 0; padding: 0; height: 100%}
    #name {display:none; width: 80px;}
    #log_container { height: 70%; overflow: auto; border: 2px solid #ccc; margin: 15px; padding: 5px;}
    #form {margin: 15px;}
    #log td, #log th {font-family: "courier new", courier, monospace; font-size: 10pt; vertical-align: top}
    #log td { white-space: pre-wrap; }
    #log th {color: blue}
    #log th.jarvis {color: red;}
    #log th {text-align: right}
    h1 {font-size: 16pt; margin: 0; padding: 15px 15px 0 15px;}
    </style>
    <script src="js/jquery-1.11.0.min.js"></script>
</head>
<body>
    <h1><?= $jarvis ?> console</h1>

    <div id="log_container">
        <table id="log">
        <?php for ($i=0; $i<50; $i++): ?>
        <tr><td>&nbsp;</td><td></td></tr>
        <?php endfor; ?>
        </table>
    </div>

    <div id="form">
    <label for="name" id="name_label"><a href="#">anonymous:</a></label> <input type="text" id="name" value="anonymous" />
    <input type="text" name="text" id="text" />
    <button id="submit">Send</button>
    </div>

    <script>
    var jarvis = '<?php echo $jarvis; ?>';
    $(document).ready(function() {
        setname = function() {
            if ($('#name').val().trim() == '') {
                $('#name').val('Web User');
            }
            $('#name_label a').html($('#name').val() + ':');
            $('#name').hide();
            $('#name_label').show();
        };

        $('#name_label a').click(function() {
            $('#name').show();
            $('#name_label').hide();
            $('#name').select();
            return false;
        });
        $('#name').keyup(function(event) {
            if (event.which == 13) {
                setname();
            }
        });
        $('#text').keyup(function(event) {
            if (event.which == 13) {
                $('#submit').click();
            }
        });
        $('#submit').click(function() {
            setname();
            $('#log').append('<tr><th>' + $('<div/>').text($('#name').val()).html() + '</th><td>' + $('<div/>').text($('#text').val()).html() + '</td></tr>');
            $("#log_container").scrollTop($("#log_container")[0].scrollHeight);
            $.post('post.php', {text: $('#text').val(), name: $('#name').val()}, function(result) {
                if (result) {
                    $('#log').append('<tr><th class="jarvis">' + jarvis + '</th><td>' + $('<div/>').text(result).html() + '</td></tr>');
                }
                $('#log tr').slice(0, -50).remove();
                $('#text').select();
                $("#log_container").scrollTop($("#log_container")[0].scrollHeight);
            });
        });
        $('#text').select();
        $("#log_container").scrollTop($("#log_container")[0].scrollHeight);
    });
    </script>
</body>
</html>
