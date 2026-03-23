import { Component, OnInit, ChangeDetectorRef, signal } from '@angular/core';
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
  boletas = signal<any[]>([]);
  isLoading = signal(true);

  constructor(
    private boletaService: BoletaService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.cargarBoletas();
  }

  cargarBoletas() {
    this.isLoading.set(true);
    
    this.boletaService.getBoletas().subscribe({
      next: (res) => {
        try {
          if (res && res.data && Array.isArray(res.data)) {
            this.boletas.set(res.data);
          } else {
            this.boletas.set([]);
          }
        } catch (e) {
          console.error('Error procesando respuesta:', e);
          this.boletas.set([]);
        } finally {
          this.isLoading.set(false);
        }
      },
      error: (err) => {
        console.error('API Error:', err);
        Swal.fire('Error', 'No se pudo cargar el listado de boletas.', 'error');
        this.isLoading.set(false);
        this.boletas.set([]);
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

  descargarBoletaPdf(id: number) {
    Swal.fire({
      title: 'Generando PDF',
      text: 'Por favor espere...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    this.boletaService.descargarBoletaPdf(id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `boleta_${id}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        Swal.close();
      },
      error: (err) => {
        console.error(err);
        Swal.fire('Error', 'No se pudo generar el reporte. Verifique que la plantilla Jasper esté configurada.', 'error');
      }
    });
  }
}
