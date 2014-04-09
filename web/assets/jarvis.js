
var speech_event;
var speech_status = false;
var listen_status = false;
var utterance;

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
                if (listen_status) {
                    utterance = new SpeechSynthesisUtterance(result);
                    speechSynthesis.speak(utterance);
                }
            }
            $('#log tr').slice(0, -50).remove();
            $('#text').select();
            $("#log_container").scrollTop($("#log_container")[0].scrollHeight);
        });
    });
    $('#text').select();
    $("#log_container").scrollTop($("#log_container")[0].scrollHeight);

    if ('speechSynthesis' in window) {
        $('#listen').show();
        $('#listen').click(function() {
            if (listen_status) {
                $('#listen').attr('src', 'assets/speaker-off.png');
                listen_status = false;
            } else {
                $('#listen').attr('src', 'assets/speaker-on.png');
                listen_status = true;
            }
        });
    }

    if ('webkitSpeechRecognition' in window) {
        var recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.onresult = function(event) { 
            speech_event = event;
            var i = event.resultIndex;
            if (event.results[i][0].transcript) {
                $('#text').val(event.results[i][0].transcript.trim());
                $('#submit').click();
            }
        };
        $('#speak').show();
        $('#speak').click(function() {
            if (speech_status) {
                $('#speak').attr('src', 'assets/mic-off.png');
                speech_status = false;
                recognition.stop();
            } else {
                $('#speak').attr('src', 'assets/mic-on.png');
                speech_status = true;
                recognition.start();
            }
        });
    }

});