import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/login',
    pathMatch: 'full'
  },
  {
    path: 'login',
    loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent)
  },
  {
    path: 'app',
    canActivate: [authGuard],
    loadComponent: () => import('./layout/main-layout/main-layout.component').then(m => m.MainLayoutComponent),
    children: [
      {
        path: 'dashboard',
        loadComponent: () => import('./features/dashboard/dashboard.component').then(m => m.DashboardComponent)
      },
      {
        path: 'boletas',
        loadComponent: () => import('./features/boletas/listado-boletas/listado-boletas.component').then(m => m.ListadoBoletasComponent)
      },
      {
        path: 'boletas/generar',
        loadComponent: () => import('./features/boletas/generar-boleta/generar-boleta.component').then(m => m.GenerarBoletaComponent)
      },
      {
        path: 'boletas/editar/:id',
        loadComponent: () => import('./features/boletas/generar-boleta/generar-boleta.component').then(m => m.GenerarBoletaComponent)
      },
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      }
    ]
  },
  {
    path: '**',
    redirectTo: '/login'
  }
];
