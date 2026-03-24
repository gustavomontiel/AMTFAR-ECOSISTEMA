import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DashboardService } from './dashboard.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit {
  kpis = signal<any>({
    farmacias_activas: 0,
    empleados_activos: 0,
    recaudacion_mes: 0,
    deuda_mes: 0,
    recaudacion_historica: 0
  });
  
  rankingMorosidad = signal<any[]>([]);
  isLoading = signal(true);

  constructor(private dashboardService: DashboardService) {}

  ngOnInit(): void {
    this.cargarDatos();
  }

  cargarDatos() {
    this.isLoading.set(true);
    this.dashboardService.getKpis().subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.kpis.set(res.data.kpis);
          this.rankingMorosidad.set(res.data.ranking_morosidad);
        }
        this.isLoading.set(false);
      },
      error: (err) => {
        console.error('Error cargando KPIs', err);
        Swal.fire('Error', 'No se pudieron cargar los datos del tablero.', 'error');
        this.isLoading.set(false);
      }
    });
  }
}
