# FinPilot

**FinPilot** – AI‑powered personal finance decision‑support system.

## Overview
- **Frontend**: React + Vite + TypeScript, styled with Tailwind CSS (glassmorphic fintech theme).
- **Backend**: FastAPI, SQLAlchemy (SQLite for dev, PostgreSQL compatible), JWT authentication.
- **Database**: SQLite (`finpilot.db`) for local development (swap to PostgreSQL in production).
- **Features**:
  - Dashboard with KPI cards, cash‑flow trends, category breakdowns.
  - Transaction ledger with search, pagination, recategorisation.
  - Budgets, financial goals, recurring subscription detection.
  - Automated insights & anomaly detection.
  - AI assistant chat powered by deterministic analytics (fallback) and optional OpenAI tools.
  - Secure authentication, password hashing with bcrypt, JWT.
  - File upload (CSV/Excel/PDF) with automatic parsing and categorisation.

## Quick Start (Local Development)

### Prerequisites
- **Python 3.11+**
- **Node.js 20+** (npm comes with it)
- **Git** (optional, for cloning)

### Backend Setup
```bash
# Navigate to backend folder
cd finpilot/backend

# Create virtual environment (optional but recommended)
python -m venv venv
source venv/bin/activate   # on Windows: venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Initialise the SQLite DB (seed data)
python seed.py

# Run the FastAPI server
uvicorn app.main:app --reload --port 8000
```
The API will be available at `http://localhost:8000`.

### Frontend Setup
```bash
# In a new terminal, go to the frontend folder
cd finpilot/frontend

# Install dependencies
npm install

# Run the dev server
npm run dev
```
The UI will be served at `http://localhost:5173` (Vite dev server).

### Environment Variables
Create a `.env` file in `backend` (or copy `backend/.env.example`):
```
JWT_SECRET=replace-with-a-local-secret
DATABASE_URL=sqlite:///./finpilot.db   # replace with PostgreSQL URL for prod
BACKEND_CORS_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
OPENAI_API_KEY=your-openai-key   # optional, for AI assistant
```

## Docker (Production Ready)
A `docker-compose.yml` is provided for quick containerised deployment.
```bash
# Build and start containers
docker compose up --build -d
```
- Backend runs on port **8000**.
- Frontend runs on port **5173** (or you can change the expose port).

## Testing
```bash
# Backend tests (pytest)
cd finpilot/backend
pytest
```
All tests should pass.

## Project Structure
```
finpilot/
├─ backend/               # FastAPI server
│  ├─ app/               # API routers, models, services
│  ├─ requirements.txt
│  ├─ seed.py
│  └─ finpilot.db        # SQLite DB (dev)
├─ frontend/              # React Vite app
│  ├─ src/               # Components, pages, services, types
│  ├─ index.html
│  └─ vite.config.ts
├─ docker-compose.yml
├─ README.md
└─ .env.example
```

## License
MIT – feel free to modify and extend.

---
Enjoy exploring your finances with **FinPilot**! 🚀
