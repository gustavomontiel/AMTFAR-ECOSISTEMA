import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../auth/auth.service';

export const roleGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const authService = inject(AuthService);

  if (!authService.isAuthenticated()) {
    return router.navigate(['/login']);
  }

  const requiredPermission = route.data['requiredPermission'];

  if (requiredPermission && !authService.hasPermission(requiredPermission)) {
    // Redirigir a una página de acceso denegado o al dashboard principal
    return router.navigate(['/dashboard']); 
  }

  return true;
};
