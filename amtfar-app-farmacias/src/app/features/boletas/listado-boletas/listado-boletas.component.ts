import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { BoletaService } from '../../../core/services/boleta.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-listado-boletas',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './listado-boletas.component.html',
  styleUrls: ['./listado-boletas.component.scss']
})
export class ListadoBoletasComponent implements OnInit {
  boletas: any[] = [];
  isLoading = true;

  constructor(private boletaService: BoletaService) {}

  ngOnInit(): void {
    this.cargarBoletas();
  }

  cargarBoletas() {
    this.isLoading = true;
    this.boletaService.getBoletas().subscribe({
      next: (res) => {
        this.boletas = res.data || [];
        this.isLoading = false;
      },
      error: (err) => {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar el listado de boletas.', 'error');
        this.isLoading = false;
      }
    });
  }

  getStatusBadge(estado: number) {
    switch(estado) {
      case 0: return { label: 'Borrador / En Edición', class: 'bg-slate-200 text-slate-700 border-slate-300' };
      case 1: return { label: 'Generada', class: 'bg-yellow-100 text-yellow-800 border-yellow-300' };
      case 2: return { label: 'Impresa', class: 'bg-blue-100 text-blue-800 border-blue-300' };
      case 3: return { label: 'Pagada', class: 'bg-green-100 text-green-800 border-green-300' };
      default: return { label: 'Desconocido', class: 'bg-gray-100 text-gray-800 border-gray-300' };
    }
  }

  pagarBoleta(id: number) {
    Swal.fire('Próximamente', 'Integración con botón de pago.', 'info');
  }
}
