<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-6 text-start">
                <p class="mb-0">
                    &copy; Copyright <a href="https://www.caydeesoft.com" target="_blank">Caydeesoft Solutions Limited</a>. All rights reserved.
                </p>
            </div>

        </div>
    </div>
</footer>
</div>
</div>


<script>
    window.appConfig = Object.assign({}, window.appConfig ?? {}, {
        mediaLibraryIndexUrl: @json(route('dashboard.media-library.index')),
        mediaLibraryStoreUrl: @json(route('dashboard.media-library.store')),
    });
</script>
<script src="{{ asset('assets/js/app.js?v='.(file_exists(public_path('assets/js/app.js')) ? filemtime(public_path('assets/js/app.js')) : time())) }}" type="module"></script>

@yield('footer')



</body>

</html>
