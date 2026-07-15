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
        mediaLibraryIndexUrl: @json(route('media-library.index')),
        mediaLibraryStoreUrl: @json(route('media-library.store')),
    });
</script>
<script src="{{ mix('/assets/js/app.js?v=1.0.3') }}" type="module"></script>

@yield('footer')



</body>

</html>
