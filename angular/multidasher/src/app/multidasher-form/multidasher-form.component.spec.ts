
import { fakeAsync, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherFormComponent } from './multidasher-form.component';

describe('MultidasherFormComponent', () => {
  let component: MultidasherFormComponent;
  let fixture: ComponentFixture<MultidasherFormComponent>;

  beforeEach(fakeAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MultidasherFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should compile', () => {
    expect(component).toBeTruthy();
  });
});
