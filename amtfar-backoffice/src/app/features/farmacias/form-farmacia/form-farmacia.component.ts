import { Component, EventEmitter, Input, Output, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FarmaciaService } from '../../../core/services/farmacia.service';

@Component({
  selector: 'app-form-farmacia',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './form-farmacia.component.html'
})
export class FormFarmaciaComponent {
  @Input() set farmacia(val: any) {
    if (val) {
      this.formData = { ...val };
      this.isEdit = true;
    } else {
      this.formData = { estado_baja: 0 };
      this.isEdit = false;
    }
  }
  @Output() guardado = new EventEmitter<boolean>();
  @Output() cancelado = new EventEmitter<void>();

  public farmaciaService = inject(FarmaciaService);
  public formData: any = { estado_baja: 0 };
  public isEdit = false;
  public isLoading = signal(false);

  guardar() {
    this.isLoading.set(true);
    if (this.isEdit) {
      this.farmaciaService.actualizar(this.formData.id, this.formData).subscribe({
        next: () => {
          this.isLoading.set(false);
          this.guardado.emit(true);
        },
        error: (err) => {
          alert('Error: ' + (err.error?.message || 'Error al guardar'));
          this.isLoading.set(false);
        }
      });
    } else {
      this.farmaciaService.crear(this.formData).subscribe({
        next: () => {
          this.isLoading.set(false);
          this.guardado.emit(true);
        },
        error: (err) => {
          alert('Error: ' + (err.error?.message || 'Error al guardar'));
          this.isLoading.set(false);
        }
      });
    }
  }
}
