import { Routes } from '@angular/router';
import { LoginComponent } from './features/login/login.component';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent },
  { 
    path: 'dashboard', 
    loadComponent: () => import('./features/dashboard/dashboard.component').then(m => m.DashboardComponent),
    canActivate: [roleGuard],
    data: { requiredPermission: 'ver_dashboard' },
    children: [
      {
         path: 'farmacias',
         loadComponent: () => import('./features/farmacias/listado-farmacias/listado-farmacias.component').then(m => m.ListadoFarmaciasComponent)
      },
      {
         path: 'boletas',
         loadComponent: () => import('./features/boletas-global/listado-boletas-global/listado-boletas-global.component').then(m => m.ListadoBoletasGlobalComponent)
      }
    ]
  },
  { 
    path: 'reportes', 
    loadComponent: () => import('./features/reportes/reportes.component').then(m => m.ReportesComponent),
    canActivate: [roleGuard],
    data: { requiredPermission: 'ver_reportes' }
  }
];
