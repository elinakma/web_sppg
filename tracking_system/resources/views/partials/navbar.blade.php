<nav class="navbar bg-white shadow-sm px-4 py-3">
    <button class="btn btn-light border-0" id="toggleSidebar">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="fw-semibold text-dark">
            {{ Auth::user()->name ?? 'Admin' }}
        </span>

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</nav>
