let items = [];
let cart = [];

const getCartElmId = (id) => `cart-item-${id}`;
function renderDishes(initial) {
  items = initial;
  const createDish = ({ id, title, price, image }) => `
            <div class="card px-0 " id="1">
              <img
                src="${image}"
                class="img-thumbnail border-0 mx-0 px-0 py-0"
                style="max-height: 200px; object-fit: cover;"
              />
              <div class="px-2 w-100 d-flex flex-column pt-4">
                <h5 class="mb-0">
                  ${title}
                </h5>
                <p class="mt-1 fw-semibold">
                  $${(+price).toFixed(1)}
                </p>
                <label>
                  Quantity
                  <input
                    name="quantity"
                    type="number"
                    id="quantity-${id}"
                    value="1"
                    max="100"
                    min="1"
                    class="w-100 form-control"
                  />
                </label>
                <label class="mt-4">
                  Special instructions
                  <textarea
                    name="instructions"
                    style="resize: none;"
                    id="instructions-${id}"
                    rows="2"
                    class="w-100 form-control"
                  ></textarea>
                </label>
                <button
                  onclick="addToCart(this)"
                  data-id="${id}"
                  class="btn btn-primary mb-2 w-100 mt-4"
                >
                  Add to cart
                </button>
              </div>
            </div>
          </div>
        `;
  const container = document.getElementById("dishes");
  items.forEach((item) => {
    const element = document.createElement("div");
    element.id = `dish-${item.id}`;
    element.className = "col-12 col-lg-4 col-md-4 col-sm-6 py-2 px-2";
    element.innerHTML = createDish(item);
    container.appendChild(element);
  });
}


function renderItem(item) {
  const createItem = ({ id, title, price, quantity, instructions }) => `
            <div class="d-flex flex-row align-items-center px-2">
              <div class="col d-flex flex-row align-items-center">
                <img
                  src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?crop=entropy&cs=tinysrgb&fm=jpg&ixlib=rb-1.2.1&q=80&raw_url=true&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1760"
                  class="img-thumbnail border-0 mx-0 px-0 py-0"
                  style="height: 60px;"
                />
                <div class="ps-2">
                  <p class="my-0 fw-semibold">
                    ${title}
                  </p>
                   <p class="mb-0 mt-1 fw-medium text-secondary">
                    ${instructions}
                  </p>
                  <button data-id="${id}" onclick="removeCartItem(this)" class="btn btn-link text-danger btn-sm ps-0">
                    Remove
                  </button>
                </div>
              </div>
              <div>
                <p class="my-0 fw-medium col fs-5">
                  $${+price * +quantity}
                </p>
                <p class="my-0 text-secondary fw-medium fs-6 col">
                  Qty: ${quantity}
                </p>
              </div>
            </div>
          `;
  const id = getCartElmId(item.id);
  const content = createItem(item);
  const cartContainer = document.getElementById("cart");
  // get the item
  const cartItem = document.getElementById(id);
  // if it exist, update it
  if (cartItem) {
    cartItem.innerHTML = createItem(item);
  } else {
    // if not, create new one and append to children
    const div = document.createElement("div");
    div.className = "card";
    div.id = id;
    div.innerHTML = content;
    cartContainer.appendChild(div);
  }
}
function updateReceipt() {
  const { total, totalBeforeTax } = cart.reduce(
    (acc, item) => {
      const totalBeforeTax = acc.total + item.price * item.quantity;
      const total = totalBeforeTax * 0.16 + totalBeforeTax;
      return {
        total,
        totalBeforeTax,
      };
    },
    { total: 0, totalBeforeTax: 0 }
  );
  document.getElementById("total-bt").innerText = `$${totalBeforeTax.toFixed(
    1
  )}`;
  document.getElementById("total").innerText = `$${total.toFixed(1)}`;
  document
    .getElementById("cart-items-input")
    .setAttribute("value", JSON.stringify(cart));
}
function addToCart(target) {
  const id = target.getAttribute("data-id");
  const quantity = +document
    .getElementById(`quantity-${id}`)
    .getAttribute("value");
  const instructions = document.getElementById(`instructions-${id}`).getAttribute('value');
  const item = items.find((i) => +i.id === +id);
  const itemIndex = cart.findIndex((i) => +i.id === +id);
  const cartItem = {
    ...item,
    quantity,
    instructions: instructions,
  };
  if (itemIndex > -1) {
    cartItem.quantity = cart[itemIndex].quantity += +quantity;
    cart[itemIndex] = cartItem;
  } else {
    cart.push(cartItem);
  }
  renderItem(cartItem);
  document.getElementById("cart-items-input").value = JSON.stringify(cart);
  updateReceipt();
}
function removeCartItem(target) {
  const id = target.getAttribute("data-id");
  cart = cart.filter((i) => +i.id !== +id);
  document
    .getElementById("cart")
    .removeChild(document.getElementById(getCartElmId(id)));
  updateReceipt();
}
