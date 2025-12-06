import { Outlet } from 'react-router-dom';

function AuthLayout() {
  return (
    <div className="auth-page">
      <div className="auth-container">
        <div className="auth-welcome-text">Welcome to RefurbWorks</div>
        <div className="auth-card">
          <Outlet />
        </div>
      </div>
    </div>
  );
}

export default AuthLayout;


