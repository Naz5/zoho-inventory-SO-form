## Getting Started

### 1. Clone the repository
```bash
git clone <repository-url>
cd zoho-inventory-SO-form
```

### 2. Backend Setup (Laravel)

The backend is containerized for easy setup.

1. **Configure Environment Variables**:
   ```bash
   cd backend
   cp .env.example .env
   ```
   Fill in your Zoho API credentials in the `.env` file:
   - `ZOHO_CLIENT_ID`
   - `ZOHO_CLIENT_SECRET`
   - `ZOHO_REFRESH_TOKEN`
   - `ZOHO_ORG_ID`

2. **Run with Docker**:
   ```bash
   cd ..
   docker-compose up --build
   ```
   The backend will be available at `http://localhost:8000`.

3. **Initialize Database**:
   ```bash
   docker exec -it zoho-backend php artisan migrate
   ```

### 3. Frontend Setup (Vue 3)

The frontend runs natively using Vite.

1. **Install Dependencies**:
   ```bash
   cd frontend
   npm install
   ```

2. **Run Development Server**:
   ```bash
   npm run dev
   ```
   The frontend will be available at `http://localhost:5173`.

---
