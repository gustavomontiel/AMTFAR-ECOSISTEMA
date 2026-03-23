import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth/auth.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  template: `
    <div class="flex h-screen bg-slate-100">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md flex flex-col">
        <div class="p-4 border-b">
          <h2 class="text-xl font-bold text-amtfar-primary">Backoffice</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
          <a routerLink="/dashboard" class="block py-2 px-4 rounded hover:bg-slate-50 text-slate-700">Inicio</a>
          
          @if (authService.hasPermission('gestionar_usuarios')) {
          <a routerLink="/usuarios" class="block py-2 px-4 rounded hover:bg-slate-50 text-slate-700">
            Gestionar Usuarios
          </a>
          }

          @if (authService.hasPermission('gestionar_farmacias')) {
          <a routerLink="/dashboard/farmacias" class="block py-2 px-4 rounded hover:bg-slate-50 text-slate-700">
            Directorio de Farmacias
          </a>
          <a routerLink="/dashboard/boletas" class="block py-2 px-4 rounded hover:bg-slate-50 text-slate-700">
            Visor de Boletas
          </a>
          }
          
          @if (authService.hasPermission('ver_reportes')) {
          <a routerLink="/reportes" class="block py-2 px-4 rounded hover:bg-slate-50 text-slate-700">
            Reportes
          </a>
          }
        </nav>
        <div class="p-4 border-t">
          <button (click)="authService.logout()" class="w-full text-left text-red-600 hover:text-red-800 px-4 py-2">
            Cerrar Sesión
          </button>
        </div>
      </aside>

      <!-- Content -->
      <main class="flex-1 p-8 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-4">Bienvenido al Dashboard</h1>
        <p class="text-slate-600">Has accedido correctamente con tu rol y permisos.</p>
        <router-outlet></router-outlet>
      </main>
    </div>
  `
})
export class DashboardComponent {
  public authService = inject(AuthService);
}
