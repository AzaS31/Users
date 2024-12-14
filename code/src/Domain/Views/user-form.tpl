<form action="/user/update/{{ user.getUserId() }}" method="post">
  <input id="csrf_token" type="hidden" name="csrf_token" value="{{ csrf_token }}">
  
  <!-- Скрытое поле для ID пользователя -->
  <input type="hidden" name="id_user" value="{{ user.getUserId() }}">
  
  <p>
    <label for="user-name">Имя:</label>
    <input id="user-name" type="text" name="name" value="{{ user.getUserName() }}">
  </p>
  <p>
    <label for="user-lastname">Фамилия:</label>
    <input id="user-lastname" type="text" name="lastname" value="{{ user.getUserLastName() }}">
  </p>
  <p>
    <label for="user-birthday">День рождения:</label>
    <input id="user-birthday" type="text" name="birthday" value="{{ user.getUserBirthday() | date('d.m.Y') }}" placeholder="ДД-ММ-ГГГГ">
  </p>
  <p><input type="submit" value="Сохранить"></p>
</form>
