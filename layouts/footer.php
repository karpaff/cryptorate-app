
<footer>
            &copy; 2024 Валютный Трекер
    </footer>
</body>
<script>
    // Измеряем высоту заголовка и навигационного меню и устанавливаем отступ для контейнера с контентом
    window.onload = function() {
        var headerHeight = document.querySelector('header').offsetHeight;
        var navHeight = document.querySelector('nav').offsetHeight;
        var totalHeight = headerHeight + navHeight + 10;
        document.querySelector('.content').style.marginTop = totalHeight + 'px';

    };
    </script>

</html>
