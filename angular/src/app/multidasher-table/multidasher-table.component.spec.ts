
import { fakeAsync, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherTableComponent } from './multidasher-table.component';

describe('MultidasherTableComponent', () => {
  let component: MultidasherTableComponent;
  let fixture: ComponentFixture<MultidasherTableComponent>;

  beforeEach(fakeAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherTableComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MultidasherTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should compile', () => {
    expect(component).toBeTruthy();
  });
});
