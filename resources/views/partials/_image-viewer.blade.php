@push('script')
    <script>
        $(document).ready(function () {
            const preview = [
                {
                    id: 'photo',
                    name: 'photo-preview',
                },
                {
                    id: 'photo-1',
                    name: 'photo-preview-1',
                },
                {
                    id: 'photo-2',
                    name: 'photo-preview-2',
                },
                {
                    id: 'photo-3',
                    name: 'photo-preview-3',
                },
                {
                    id: 'photo-4',
                    name: 'photo-preview-4',
                },

            ];
            preview.forEach(function (item) {
                $('#' + item.id).on('change', function (event) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        $('#' + item.name).attr('src', e.target.result);
                    };
                    reader.readAsDataURL(event.target.files[0]);
                });
            });
        });
    </script>
@endpush
