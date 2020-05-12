jQuery.fn.forceNumeric = function () {
    return this.each(function () {
        $(this).keydown(function (e) {
            var key = e.which || e.keyCode;

            if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
                    // numbers   
                key >= 48 && key <= 57 ||
                // Numeric keypad
                key >= 96 && key <= 105 ||
                // comma, period and minus, . on keypad
                key == 190 || key == 188 || key == 109 || key == 110 ||
                // Backspace and Tab and Enter
                key == 8 || key == 9 || key == 13 ||
                // Home and End
                key == 35 || key == 36 ||
                // left and right arrows
                key == 37 || key == 39 ||
                // Del and Ins
                key == 46 || key == 45 
            )
                return true;

            return false;
        });
    });
}

jQuery.fn.checkAccountNumberTrigger = function (posn = '') { 
 
    var selector = $("#account_number"+posn);  
    if (typeof selector === 'undefined') {
        var selector = $(this); 
    } 

    $( this ).change(function(event) { 

        var account_number = $("#account_number"+posn).val(); 
        var bank_code      = $("select#bank_code"+posn).children("option:selected").val(); 

        $('#avm_'+posn).remove();
        $(selector).closest('.account_validator').append('<small class="text-info" id="avm_'+posn+'">Checking for account...</small>');

        if (account_number.length >= 10) { 
            console.log('Checking Account Number'); 

            $.post(link('main/verify_account_number'), 
                {account_number:account_number,bank_code:bank_code}, 
                function(data, textStatus, xhr) {
                    var mclass = data.status == 200 ? 'success' : 'danger';
                    $('#avm_'+posn).remove();
                    $(selector).closest('.account_validator').append('<small class="text-'+mclass+'" id="avm_'+posn+'">'+data.name+'</small>');
            });
            console.log(account_number.length, account_number, bank_code,); 
        } 
    });
}

jQuery.fn.checkAccountNumber = function (posn = '') { 

    var selector = $(this).selector; 
    if (typeof selector === 'undefined') {
        var selector = $(this); 
    } 

    $( this ).keyup(function(event) {

        var account_number = $("#account_number"+posn).val(); 
        var bank_code      = $("select#bank_code"+posn).children("option:selected").val(); 
        var key            = event.which || event.keyCode;
        var alKey          = key >= 48 && key <= 57 || key >= 96 && key <= 105 || key == 8 || key == 13 || key == 46 || key == 45;
            
        if (account_number.length >= 10 && alKey) { 
            console.log('Checking Account Number'); 
            var message = '<small class="text-info" id="avm_'+posn+'">Checking for account...</small>';
            $('#avm_'+posn).remove();
            $(selector).closest('.account_validator').append(message);

            $.post(link('main/verify_account_number'), 
                {account_number:account_number,bank_code:bank_code}, 
                function(data, textStatus, xhr) {
                    var mclass  = data.status == 200 ? 'success' : 'danger';
                    var message = '<small class="text-'+mclass+'" id="avm_'+posn+'">'+data.name+'</small>';
                    $('#avm_'+posn).remove();
                    $(selector).closest('.account_validator').append(message);
            });
            console.log(account_number.length, account_number, bank_code,); 
        } 
    })
    .forceNumeric(); 
}
