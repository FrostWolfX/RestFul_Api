
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RESTful api</title>
</head>

<script>
    fetch('/api/controllers/read.php',)
        .then((response) => {
            if (response.status !== 200) {
                return Promise.reject();
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
            return data;
        })
        .catch((e) => console.log(`ошибка ${e}`));
</script>
<body>

</body>
</html>
