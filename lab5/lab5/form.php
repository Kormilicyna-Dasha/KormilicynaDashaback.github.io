<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"  href="style.css">
    <title> LAB5 </title>
  </head>
  <body>

    <?php 
    if(isset($_COOKIE[session_name()]) && !empty($_SESSION['login'])){
      print('<form class="logout_form" action="login.php" method="POST">
      <input type="submit" name="logout" value="Выйти"/> 
    </form>');
    }

    
    ?>


    <div class="error_messages" <?php if (empty($messages)) {print 'display="none"';} else {print 'display="block"';} ?>>

      <?php
      if (!empty($messages)) {
        print('<div id="messages">');
        foreach ($messages as $message) {
          print($message);
        }
        print('</div>');
      }
      ?>

    </div>


    <div class="formstyle" > 
      <form id="myform" class="application" action="index.php" method="POST">

        <h2> ФОРМА </h2> 

      <label> 
        ФИО: <br/>
        <input name="fio" <?php if ($errors['fio'] ) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>" />
      </label> <br/>

      <label> 
        Номер телефона: <br />
        <input name="number" type="tel" 
        <?php if ($errors['number']) {print 'class="error"';} ?> value="<?php print $values['number']; ?>"/>
      </label> <br/>
      <p class="numtext"> *используйте телефонный код +7</p>

      <label>
        E-mail: <br/>
        <input name="email" type="email" 
        <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>"/>
      </label> <br/>

      <label> 
        Дата рождения: <br/>
        <input name="birthdate" type="date" 
        <?php if ($errors['bdate']) {print 'class="error"';} ?> value="<?php print $values['bdate']; ?>"/>
      </label> <br/>
      
       Пол: <br /> 
      <label> <input type="radio" name="radio-group-1" value="male" 
      <?php if ($errors['gen']) {print 'class="error"';} ?>
      <?php if ($values['gen']=='male') {print 'checked="checked"';} ?>/> Мужской </label>
      <label> <input type="radio"  name="radio-group-1" value="female" 
      <?php if ($errors['gen']) {print 'class="error"';} ?>
      <?php if ($values['gen']=='female') {print 'checked="checked"';} ?>/> Женский</label> <br/>

      <?php 
      $user_languages = explode(",",  $values['lang']);
      ?>

      <label > 
        Любимый язык программирования: <br/>
        <select  name="languages[]" multiple="multiple" 
        <?php if ($errors['lang']) {print 'class="error"';} ?> >

        <?php 
          foreach ($allowed_lang as $lang => $value) {
            printf('<option value="%s" ', $lang);
            if(in_array($lang, $user_languages)) {
              print 'selected="selected"';
            }
            printf('>%s</option>', $value);
          }
        ?>
        
        </select>
      </label> <br/>

      <label>
        Биография: <br/>
        <textarea name="biography" <?php if ($errors['bio']) {print 'class="error"';} ?>><?php print $values['bio']; ?></textarea>
      </label> <br/>

      <label class="form-checkbox pl-2">
        <input type="checkbox" name="checkbox"
        <?php if ($errors['checkbox']) {print 'class="error"';} ?>  <?php if (!$errors['checkbox']) {print 'checked="checked"';} ?>/> 
        С контрактом ознакомлен 
      </label> <br/>

      <input type="submit" value="Сохранить"/> 
      </form>
    </div>

  
  </body>
</html>