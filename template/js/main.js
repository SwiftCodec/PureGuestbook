document.onreadystatechange = function() {

    $(document).ready(function(){

      // Generate test data. Form submit
      $('#frmDemoRows').submit(function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        var inputRowCnt = $('#rows_cnt'),
            blockStatus = $('#blockStatus'),
            iconStatus = $('#iconStatus'),
            createBtn1 = $('#createBtn1');
        $.ajax({url:"install.php?action=generate",
          type: 'POST',
          data:"p1=" + inputRowCnt.val(),
          beforeSend:function(){
            blockStatus.html('')
            iconStatus.css("display","block")
            createBtn1.prop('disabled', true);
          },
          complete:function(){
            iconStatus.css("display","none")
            createBtn1.prop('disabled', false);
          },
          success:function(result) {
            if (result == 0) {
              blockStatus.html('<span style="color: red;">Необходимо значение от 10 до 10000</span>')
            } else {
              blockStatus.html('<span style="color: green;">' + result + '</span>')
            }
          },
          error:function(e){
            blockStatus.html('<span style="color: red;">' + e + '</span>')
          }
        })
        return false;
      });

      // Add comment. Form submit
      $('#frmAddMsg').submit(function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        var form    = $(this),
            btnSend = $('#btnMsgSend'),
            statusBlock = $('#blockStatus'),
            statusMsg1 = $('#statusMsg1'),
            statusMsg2 = $('#statusMsg2'),
            iconStatus    = $('#icon_status');
        $.ajax({url:"index.php?action=message_add",
          type: 'POST',
          data:form.serialize(),
          beforeSend:function(){
            statusBlock.css("display","none")
            statusMsg1.html('')
            statusMsg2.html('')
            iconStatus.css("display","block")
            btnSend.prop('disabled', true)
          },
          complete:function(){
            iconStatus.css("display","none")
            btnSend.prop('disabled', false)
          },
          success:function(result) {
            if (result=='success') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Успех!</h4>')
              statusMsg2.html('Ваше сообщение отправлено')
              $('#message').val('');
              $('#tags').val('');
            } else if (result=='data_required') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Проверьте введённые данные и повторите ещё раз')
            } else if (result=='invalid_username') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Неверное имя пользователя. Только латинские символы и цифры, от 8 символов до 32 символов.')
            } else if (result=='invalid_email') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Неверный E-Mail.')
            } else if (result=='invalid_message') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Сообщение должно содержать от 10 до 2048 символов.')
            } else if (result=='invalid_homepage') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Домашняя страница не может превышать 128 символов.')
            } else if (result=='invalid_tags') {
              statusBlock.css("display", "block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Длина тегов не может превышать 256 символов.')
            } else {
              statusBlock.css("display","block")
              statusMsg1.html('<h4 class="alert-heading">Ошибка!</h4>')
              statusMsg2.html('Невозможно получить данные от сервера ')
            }
          },
          error:function(e){
            statusBlock.css("display","block")
            statusMsg1.html('Ошибка!')
            statusMsg2.html(e)
            console.log(e)
          }
        });
        return false;
      })

/*
      $('#frmAddMsg').on('submit', function(e){
        var form    = $(this),
            input   = $('.check-recover-email'),
            span    = $('.recover-error'),
            email   = input.val();
        span.text('');
        e.preventDefault(); // <=================== Here
        $.ajax({
          url: 'ajax/check-email',
          type: 'POST',
          data: form.serialize(),
          success: function(response){
            if ( response == 0 ) {
              // ============================ Not here, this would be too late
              span.text('email does not exist');
            }
          }
        });
      });
*/

    });



}

