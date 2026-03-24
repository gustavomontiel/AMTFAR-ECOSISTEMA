import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { DashboardService, PeriodoDashboard, ResumenCategoria } from '../../core/services/dashboard.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  isLoading = signal(true);
  
  private dashboardService = inject(DashboardService);
  private router = inject(Router);

  // Estado General
  tieneDeuda = signal(false);
  deudaTotal = signal(0);
  
  // Períodos 
  periodos = signal<PeriodoDashboard[]>([]);
  
  // Resumen del Padrón (última DDJJ)
  totalEmpleados = signal(0);
  desgloseCategorias = signal<ResumenCategoria[]>([]);

  ngOnInit() {
    this.dashboardService.getDashboardData().subscribe({
      next: (res) => {
        if (res.status === 'success') {
          const data = res.data;
          this.tieneDeuda.set(data.tieneDeuda);
          this.deudaTotal.set(data.deudaTotal);
          this.periodos.set(data.periodos);
          this.totalEmpleados.set(data.totalEmpleados);
          this.desgloseCategorias.set(data.desgloseCategorias);
        }
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Error cargando dashboard', err);
        this.isLoading.set(false);
      }
    });
  }

  iniciarCarga(periodo: PeriodoDashboard) {
    if (periodo.bloqueado) return;
    // Navegar a generar boleta pasando el periodo ID
    console.log('Navegando a carga de nómina para', periodo.mes);
    this.router.navigate(['/app/boletas/generar']);
  }

  pagarBoleta(periodo: PeriodoDashboard) {
    console.log('Abriendo pasarela o opciones para boleta', periodo.boletaId);
    // TODO: Implementar pago real
  }
}
