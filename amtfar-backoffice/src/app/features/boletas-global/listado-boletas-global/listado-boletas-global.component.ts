import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BoletaGlobalService } from '../../../core/services/boleta-global.service';

@Component({
  selector: 'app-listado-boletas-global',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './listado-boletas-global.component.html'
})
export class ListadoBoletasGlobalComponent implements OnInit {
  public boletaService = inject(BoletaGlobalService);
  public isLoading = signal<boolean>(true);

  ngOnInit() {
    this.cargarBoletas();
  }

  cargarBoletas() {
    this.isLoading.set(true);
    this.boletaService.getListadoGlobal().subscribe({
      next: () => this.isLoading.set(false),
      error: () => this.isLoading.set(false)
    });
  }

  descargarPdf(idBoleta: number) {
    this.boletaService.descargarPdf(idBoleta).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `boleta_global_${idBoleta}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      },
      error: () => alert('Error al descargar el PDF.')
    });
  }
}
