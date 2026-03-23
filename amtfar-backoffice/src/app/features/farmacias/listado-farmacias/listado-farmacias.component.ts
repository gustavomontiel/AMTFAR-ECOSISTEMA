import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FarmaciaService } from '../../../core/services/farmacia.service';
import { FormFarmaciaComponent } from '../form-farmacia/form-farmacia.component';

@Component({
  selector: 'app-listado-farmacias',
  standalone: true,
  imports: [CommonModule, FormFarmaciaComponent],
  templateUrl: './listado-farmacias.component.html'
})
export class ListadoFarmaciasComponent implements OnInit {
  public farmaciaService = inject(FarmaciaService);
  
  public isLoading = signal<boolean>(true);
  public isModalOpen = signal<boolean>(false);
  public selectedFarmacia = signal<any>(null);

  ngOnInit() {
    this.cargarFarmacias();
  }

  cargarFarmacias() {
    this.isLoading.set(true);
    this.farmaciaService.getListado().subscribe({
      next: () => this.isLoading.set(false),
      error: () => this.isLoading.set(false)
    });
  }

  abrirModalNuevo() {
    this.selectedFarmacia.set(null);
    this.isModalOpen.set(true);
  }

  abrirModalEditar(farmacia: any) {
    this.selectedFarmacia.set(farmacia);
    this.isModalOpen.set(true);
  }

  cerrarModal() {
    this.isModalOpen.set(false);
    this.selectedFarmacia.set(null);
  }

  onGuardado(event: boolean) {
    if (event) {
      this.cargarFarmacias();
      this.cerrarModal();
    }
  }

  darDeBaja(farmacia: any) {
    if (confirm(`¿Seguro que desea dar de BAJA LÓGICA a la farmacia ${farmacia.razon_social}?`)) {
      this.farmaciaService.bajaLaboral(farmacia.id).subscribe({
        next: () => {
          alert('Farmacia inactiva.');
          this.cargarFarmacias();
        },
        error: (err) => alert('No se pudo desactivar. ' + (err.error?.message || 'Error'))
      });
    }
  }
}
