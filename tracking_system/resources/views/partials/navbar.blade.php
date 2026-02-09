<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <div class="d-flex align-items-center ms-auto gap-3">
            <span class="text-muted">
                {{ Auth::user()->name ?? 'Admin' }}
            </span>

            <form action="{{ route('logout') }}" method="POST" class="d-flex align-items-center m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>
