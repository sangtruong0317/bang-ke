@extends('layout')

@section('title', 'Chọn xe và kho')

@section('content')

@php
    $truckCount = count($tree);
    $warehouseCount = collect($tree)->flatten()->count();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Chọn xe và kho</h3>
        <div class="text-muted">
            Chọn các kho cần xuất bảng kê
        </div>
    </div>

    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Upload file khác
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card app-card">
            <div class="card-body">
                <div class="text-muted small">Số xe</div>
                <div class="h3 mb-0">{{ $truckCount }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card app-card">
            <div class="card-body">
                <div class="text-muted small">Số kho</div>
                <div class="h3 mb-0">{{ $warehouseCount }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('generate') }}" id="generateForm">
    @csrf

    <div class="card app-card mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input
                    type="text"
                    id="searchTruck"
                    class="form-control"
                    placeholder="Tìm số xe..."
                >
            </div>
        </div>
    </div>

    <div class="mb-3 d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="checkAll">
            <i class="bi bi-check2-square"></i>
            Chọn tất cả
        </button>

        <button type="button" class="btn btn-outline-secondary btn-sm" id="uncheckAll">
            <i class="bi bi-square"></i>
            Bỏ chọn tất cả
        </button>
    </div>

    @foreach($tree as $truck => $warehouses)
        <div class="card truck-card mb-3 truck-block" data-truck="{{ strtolower($truck) }}">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="fw-bold mb-0">
                        <input type="checkbox" class="truck-check form-check-input me-2">
                        <i class="bi bi-truck text-primary"></i>
                        {{ $truck }}
                    </label>

                    <span class="badge text-bg-light">
                        {{ count($warehouses) }} kho
                    </span>
                </div>
            </div>

            <div class="card-body">
                @foreach($warehouses as $warehouse)
                    <label class="warehouse-item d-block">
                        <input
                            type="checkbox"
                            class="warehouse-check form-check-input me-2"
                            name="warehouse[]"
                            value="{{ $truck }}|{{ $warehouse }}"
                        >
                        {{ $warehouse }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="sticky-bottom bg-light py-3">
        <button class="btn btn-success w-100 py-3 fw-bold" id="exportBtn">
            <i class="bi bi-file-earmark-excel"></i>
            Xuất bảng kê Excel
        </button>
    </div>
</form>

@endsection

@section('script')
<script>
document.querySelectorAll('.truck-check').forEach(function (truckCheck) {
    truckCheck.addEventListener('change', function () {
        const card = this.closest('.truck-block');

        card.querySelectorAll('.warehouse-check').forEach(function (checkbox) {
            checkbox.checked = truckCheck.checked;
        });
    });
});

document.querySelectorAll('.warehouse-check').forEach(function (warehouseCheck) {
    warehouseCheck.addEventListener('change', function () {
        const card = this.closest('.truck-block');
        const all = card.querySelectorAll('.warehouse-check');
        const checked = card.querySelectorAll('.warehouse-check:checked');
        const truck = card.querySelector('.truck-check');

        truck.checked = all.length === checked.length;
    });
});

document.getElementById('checkAll').addEventListener('click', function () {
    document.querySelectorAll('.truck-check, .warehouse-check').forEach(cb => cb.checked = true);
});

document.getElementById('uncheckAll').addEventListener('click', function () {
    document.querySelectorAll('.truck-check, .warehouse-check').forEach(cb => cb.checked = false);
});

document.getElementById('searchTruck').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('.truck-block').forEach(function (card) {
        const truck = card.dataset.truck;
        card.style.display = truck.includes(keyword) ? '' : 'none';
    });
});

document.getElementById('generateForm').addEventListener('submit', function () {
    const btn = document.getElementById('exportBtn');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tạo Excel...';
});
</script>
@endsection