import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateRecepientComponent } from './create-recipient.component';

describe('CreateRecepientComponent', () => {
  let component: CreateRecepientComponent;
  let fixture: ComponentFixture<CreateRecepientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CreateRecepientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateRecepientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
