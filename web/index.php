<?php
require '../bootstrap.php';
if (isset($jarvis_config['name']) && $jarvis_config['name']) {
    $jarvis = htmlspecialchars($jarvis_config['name']);
} else {
    $jarvis = "jarvis";
}
?>
<html>
<head>
    <title><?= $title ?></title>
    <style>
    body {font-family: helvetica, arial, sans-serif; font-size: 10pt;}
    #name {display:none; width: 80px;}
    #log_container { height: 70%; overflow: auto; border: 2px solid #ccc; margin-bottom: 10px;}
    #log td, #log th {white-space: pre-wrap; font-family: "courier new", courier, monospace; font-size: 10pt; vertical-align: top}
    #log th {color: blue}
    #log th.jarvis {color: red;}
    #log th {text-align: right}
    h1 {font-size: 16pt; margin: 0 0 10px 0;}
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
