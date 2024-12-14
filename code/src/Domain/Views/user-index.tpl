<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Фамилия</th>
            <th>День Рождения</th>
        </tr>
    </thead>
    <tbody>
        {% for user in users %}
            <tr>
                <td>{{ user.getUserId() }}</td>
                <td>{{ user.getUserName() }}</td>
                <td>{{ user.getUserLastName() }}</td>
                <td>{{ user.getUserBirthday() | date('d.m.Y') }}</td>
                <td>
                    <a href="/user/edit/{{ user.getUserId() }}">Обновить данные</a>
                    <a href="/user/delete/{{ user.getUserId() }}">Удалить</a>
                </td>
            </tr>
        {% endfor %}
    </tbody>
</table>
