@if(isset($uploads) && $uploads->isNotEmpty())
    <div class="mt-4">
        <h5 class="mb-3">Recent migration jobs</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Message</th>
                        <th>Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uploads as $upload)
                        <tr>
                            <td>{{ $upload->original_name }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'text-bg-secondary' => $upload->status === 'pending',
                                    'text-bg-info' => $upload->status === 'processing',
                                    'text-bg-success' => $upload->status === 'completed',
                                    'text-bg-danger' => $upload->status === 'failed',
                                ])>
                                    {{ ucfirst($upload->status) }}
                                </span>
                            </td>
                            <td class="migration-progress-cell">
                                <div class="progress" role="progressbar" aria-label="Migration progress" aria-valuenow="{{ $upload->progress }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $upload->progress }}%">{{ $upload->progress }}%</div>
                                </div>
                            </td>
                            <td>{{ $upload->error ?: $upload->message }}</td>
                            <td>{{ $upload->created_at?->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
