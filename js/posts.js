const posts = async () => {
    const paramsString = document.location;
    if (paramsString.pathname === '/product.php') {
        let searchParams = new URLSearchParams(paramsString.search);
        let id = searchParams.get('id') ?? 0;
        await fetch(`/api/product/${id}`)
            .then((response) => {
                if (response.status !== 200) {
                    return Promise.reject();
                }
                return response.json();
            })
            .then((posts) => {
                if (posts.status) {
                    const post = posts.message;
                    document.querySelector('.post-list').innerHTML += `
                        <div class="card">
                            <div class="card-body" data-id="${post.id}">
                                <h5 class="card-title">${post.name}</h5>
                                <p class="card-text">${post.description}</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch((e) => console.log(`ошибка ${e}`));
    } else {
        await fetch('/api/products')
            .then((response) => {
                if (response.status !== 200) {
                    return Promise.reject();
                }
                return response.json();
            })
            .then((posts) => {
                posts.message.forEach((post) => {
                    document.querySelector('.post-list').innerHTML += `
                <div class="card">
                    <div class="card-body" data-id="${post.id}">
                        <h5 class="card-title">${post.name}</h5>
                        <p class="card-text">${post.description}</p>
                        <a href="/product.php?id=${post.id}" class="card-link">Подробнее</a>
                        <a href="#" class="card-link deletePost">Удалить</a>
                    </div>
                </div>
            `;
                });
            })
            .then(() => {
                const posts = document.querySelectorAll('.deletePost');
                if (posts) {
                    posts.forEach((element) => {
                        element.addEventListener('click', () => {
                            const parent = element.parentNode;
                            const card = parent.parentNode;
                            const id = parent.dataset.id;
                            fetch(`/api/product/${id}`, {
                                method: 'DELETE',
                            })
                                .then((response) => {
                                    if (response.status !== 200) {
                                        return Promise.reject();
                                    }
                                    return response.json();
                                })
                                .then((response) => {
                                    card.classList.add('d-none');
                                    console.log(response)
                                })
                        })
                    });
                }
                
            })
            .catch((e) => console.log(`ошибка ${e}`));
    }
}

export default posts;