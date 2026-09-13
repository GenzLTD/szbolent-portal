/**
 * 深伯乐 移动端汉堡菜单
 */
(function() {
    var toggle = document.querySelector('.bolent-mobile-toggle');
    var nav = document.querySelector('.bolent-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('bolent-nav-open');
            toggle.classList.toggle('active');
        });

        // 点击导航链接后关闭菜单
        var links = nav.querySelectorAll('a');
        links.forEach(function(link) {
            link.addEventListener('click', function() {
                nav.classList.remove('bolent-nav-open');
                toggle.classList.remove('active');
            });
        });

        // 点击外部关闭菜单
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('bolent-nav-open');
                toggle.classList.remove('active');
            }
        });
    }
})();
