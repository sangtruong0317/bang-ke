@extends('layout')

@section('title', 'Upload Excel')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="card app-card">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <div class="display-5 text-primary mb-2">
                        <i class="bi bi-file-earmark-excel"></i>
                    </div>

                    <h3 class="fw-bold">Upload file Excel</h3>
                    <p class="text-muted mb-0">
                        Chọn file dữ liệu để tạo bảng kê chành
                    </p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('upload') }}" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <label class="upload-box w-100 mb-3" for="excelInput">
                        <i class="bi bi-cloud-arrow-up display-4 text-primary"></i>

                        <div class="mt-3 fw-semibold">
                            Bấm để chọn file Excel
                        </div>

                        <div class="text-muted small mt-1">
                            Hỗ trợ .xlsx, .xls
                        </div>

                        <div class="mt-3 text-success fw-semibold" id="fileName"></div>
                    </label>

                    <input
                        type="file"
                        name="excel"
                        id="excelInput"
                        class="d-none"
                        accept=".xlsx,.xls"
                        required
                    >

                    <button class="btn btn-primary w-100 py-2 fw-semibold" id="submitBtn">
                        <i class="bi bi-upload"></i>
                        Đọc dữ liệu
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
const input = document.getElementById('excelInput');
const fileName = document.getElementById('fileName');
const form = document.getElementById('uploadForm');
const btn = document.getElementById('submitBtn');

input.addEventListener('change', function () {
    if (this.files.length > 0) {
        fileName.innerText = this.files[0].name;
    }
});

form.addEventListener('submit', function () {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đọc dữ liệu...';
});
</script>
@endsection